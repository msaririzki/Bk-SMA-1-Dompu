<?php

namespace App\Http\Controllers;

use App\Enums\DocumentType;
use App\Models\AcademicYear;
use App\Models\HomeVisit;
use App\Models\SchoolDocument;
use App\Models\SchoolSetting;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\ViolationCase;
use App\Services\AuditService;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DocumentController extends Controller
{
    public function index()
    {
        return view('app.documents.index', ['documents' => SchoolDocument::with('student')->latest('document_date')->paginate(20)]);
    }

    public function create(Request $r)
    {
        return view('app.documents.create', ['students' => Student::orderBy('name')->get(), 'cases' => ViolationCase::with('student')->latest()->get(), 'types' => DocumentType::cases(), 'selectedStudent' => $r->student, 'selectedCase' => $r->case]);
    }

    public function store(Request $r, AuditService $audit)
    {
        $d = $r->validate(['student_id' => 'required|exists:students,id', 'case_id' => 'nullable|exists:violation_cases,id', 'type' => 'required|in:'.implode(',', array_column(DocumentType::cases(), 'value')), 'number' => 'nullable|string|max:100', 'document_date' => 'required|date', 'subject' => 'nullable|string|max:255', 'body' => 'required|string', 'parent_name' => 'nullable|string|max:255', 'parent_contact' => 'nullable|string|max:100']);
        $student = Student::findOrFail($d['student_id']);
        $this->authorize('update', $student);
        if (! empty($d['case_id'])) {
            abort_unless(ViolationCase::whereKey($d['case_id'])->where('student_id', $student->id)->exists(), 422, 'Kasus tidak terkait dengan siswa yang dipilih.');
        }$year = AcademicYear::active();
        abort_unless($year, 422, 'Tahun pelajaran aktif belum ditentukan.');
        $doc = SchoolDocument::create(['student_id' => $d['student_id'], 'case_id' => $d['case_id'] ?? null, 'academic_year_id' => $year->id, 'created_by' => $r->user()->id, 'type' => $d['type'], 'number' => $d['number'] ?? null, 'document_date' => $d['document_date'], 'status' => 'final', 'payload' => collect($d)->except(['student_id', 'case_id', 'type', 'number', 'document_date'])->all()]);
        $audit->record('document.created', $doc, null, $doc->toArray());

        return redirect()->route('documents.show', $doc)->with('success', 'Dokumen berhasil dibuat.');
    }

    public function show(SchoolDocument $document)
    {
        $this->authorize('view', $document);
        $document->load(['student.currentEnrollment.schoolClass', 'case.items', 'homeVisit']);

        return view('app.documents.show', compact('document'));
    }

    public function homeVisitForm(Request $r)
    {
        $student = $r->student ? Student::with('currentEnrollment.schoolClass')->find($r->student) : null;

        return view('app.documents.home-visit', ['students' => Student::with('currentEnrollment.schoolClass')->orderBy('name')->get(), 'student' => $student, 'teachers' => Teacher::orderBy('name')->get()]);
    }

    public function homeVisitStore(Request $r, AuditService $audit)
    {
        $d = $r->validate(['student_id' => 'required|exists:students,id', 'case_id' => 'nullable|exists:violation_cases,id', 'number' => 'nullable|string|max:100', 'visit_date' => 'required|date', 'address' => 'nullable|string', 'parent_name' => 'nullable|string|max:255', 'problem' => 'required|string', 'purpose' => 'required|string', 'met_with' => 'nullable|string|max:255', 'result' => 'required|string', 'follow_up' => 'required|string', 'counselor_name' => 'required|string|max:255', 'counselor_nip' => 'nullable|string|max:50', 'homeroom_name' => 'required|string|max:255', 'homeroom_nip' => 'nullable|string|max:50', 'coordinator_name' => 'required|string|max:255', 'coordinator_nip' => 'nullable|string|max:50', 'place' => 'required|string|max:100']);
        $student = Student::with('currentEnrollment.schoolClass')->findOrFail($d['student_id']);
        $this->authorize('update', $student);
        if (! empty($d['case_id'])) {
            abort_unless(ViolationCase::whereKey($d['case_id'])->where('student_id', $student->id)->exists(), 422, 'Kasus tidak terkait dengan siswa yang dipilih.');
        }$year = AcademicYear::active();
        abort_unless($year, 422, 'Tahun pelajaran aktif belum ditentukan.');
        $doc = DB::transaction(function () use ($d, $student, $r, $year) {
            $doc = SchoolDocument::create(['student_id' => $student->id, 'case_id' => $d['case_id'] ?? null, 'academic_year_id' => $year->id, 'created_by' => $r->user()->id, 'type' => DocumentType::HomeVisit, 'number' => $d['number'] ?? null, 'document_date' => $d['visit_date'], 'status' => 'final', 'payload' => []]);
            HomeVisit::create($d + ['document_id' => $doc->id, 'counselee_name' => $student->name, 'class_name' => $student->currentEnrollment?->schoolClass?->name ?? '-', 'gender' => $student->gender]);

            return $doc;
        });
        $audit->record('home_visit.created', $doc, null, $doc->load('homeVisit')->toArray());

        return redirect()->route('documents.show', $doc)->with('success', 'Laporan home visit dibuat.');
    }

    public function pdf(SchoolDocument $document)
    {
        $this->authorize('view', $document);
        $document->load(['student.currentEnrollment.schoolClass', 'case.items', 'homeVisit', 'creator.teacher']);
        $html = view($document->type === DocumentType::HomeVisit ? 'pdf.home-visit' : 'pdf.document', ['document' => $document, 'settings' => SchoolSetting::pluck('value', 'key')])->render();
        $options = new Options;
        $options->set('isRemoteEnabled', false);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4');
        $dompdf->render();

        return response($dompdf->output(), 200, ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'attachment; filename="'.str($document->type->value.'-'.$document->student->name)->slug().'.pdf"']);
    }

    public function recap(Student $student)
    {
        $this->authorize('view', $student);
        $year = AcademicYear::active();
        $student->load(['currentEnrollment.schoolClass', 'cases' => fn ($q) => $q->with('items')->where('academic_year_id', $year->id)->where('status', '!=', 'cancelled')->orderBy('occurred_at')]);
        $html = view('pdf.recap', ['student' => $student, 'year' => $year, 'settings' => SchoolSetting::pluck('value', 'key')])->render();
        $dompdf = new Dompdf;
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4');
        $dompdf->render();

        return response($dompdf->output(),200,['Content-Type' => 'application/pdf', 'Content-Disposition' => 'attachment; filename="rekap-'.$student->temporary_id.'.pdf"']);
    }
}
