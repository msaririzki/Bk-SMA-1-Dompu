<?php

namespace App\Http\Controllers;

use App\Enums\CaseStatus;
use App\Enums\FollowUpType;
use App\Models\AcademicYear;
use App\Models\Attachment;
use App\Models\Student;
use App\Models\ViolationCase;
use App\Models\ViolationCategory;
use App\Models\ViolationInstrument;
use App\Services\AttachmentService;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ViolationCaseController extends Controller
{
    public function index(Request $r)
    {
        $cases = ViolationCase::with(['student.currentEnrollment.schoolClass', 'items', 'creator'])->when($r->q, function ($query, $term) {
            $query->whereHas('student', function ($student) use ($term) {
                $student->where(function ($identity) use ($term) {
                    $identity->where('name', 'like', "%{$term}%")->orWhere('nis', $term)->orWhere('nisn', $term)->orWhere('temporary_id', $term)->orWhereHas('aliases', fn ($alias) => $alias->where('name', 'like', "%{$term}%"));
                });
            });
        })->when($r->status, fn ($q, $status) => $q->where('status', $status))->latest('occurred_at')->paginate(20)->withQueryString();

        return view('app.cases.index', compact('cases'));
    }

    public function create(Request $r)
    {
        $selectedStudentId = old('student_id', $r->student);
        $selectedStudent = $selectedStudentId
            ? Student::with('currentEnrollment.schoolClass')->findOrFail($selectedStudentId)
            : null;
        if ($selectedStudent) {
            $this->authorize('update', $selectedStudent);
        }

        return view('app.cases.create', ['selectedStudent' => $selectedStudent, 'categories' => ViolationCategory::with(['instruments' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')])->orderBy('sort_order')->get()]);
    }

    public function store(Request $r, AuditService $audit, AttachmentService $attachments)
    {
        $d = $r->validate(['student_id' => 'required|exists:students,id', 'occurred_at' => 'required|date', 'location' => 'nullable|string|max:255', 'chronology' => 'required|string|min:10', 'instrument_ids' => 'required|array|min:1', 'instrument_ids.*' => 'exists:violation_instruments,id', 'attachments' => 'nullable|array|max:10', 'attachments.*' => 'file|mimes:jpg,jpeg,png,webp,pdf|max:10240']);
        $student = Student::findOrFail($d['student_id']);
        $this->authorize('update', $student);
        $year = AcademicYear::active();
        abort_unless($year, 422, 'Tahun pelajaran aktif belum ditentukan.');
        $case = DB::transaction(function () use ($d, $r, $year, $attachments) {
            AcademicYear::whereKey($year->id)->lockForUpdate()->firstOrFail();
            $prefix = 'BK-'.now()->format('Y').'-';
            $lastNumber = ViolationCase::withTrashed()
                ->where('case_number', 'like', $prefix.'%')
                ->orderByDesc('case_number')
                ->value('case_number');
            $sequence = $lastNumber ? ((int) Str::afterLast($lastNumber, '-')) + 1 : 1;
            $case = ViolationCase::create(['case_number' => $prefix.sprintf('%05d', $sequence), 'student_id' => $d['student_id'], 'academic_year_id' => $year->id, 'created_by' => $r->user()->id, 'occurred_at' => $d['occurred_at'], 'location' => $d['location'] ?? null, 'chronology' => $d['chronology'], 'status' => CaseStatus::Open]);
            foreach (ViolationInstrument::whereIn('id', $d['instrument_ids'])->get() as $instrument) {
                $case->items()->create(['instrument_id' => $instrument->id, 'instrument_code' => $instrument->code, 'instrument_name' => $instrument->name, 'points' => $instrument->points, 'sanction_snapshot' => $instrument->sanction]);
            }foreach ($r->file('attachments', []) as $file) {
                $attachments->store($file, $case, $r->user());
            }

            return $case;
        });
        $audit->record('case.created', $case, null, $case->load('items')->toArray());

        return redirect()->route('cases.show', $case)->with('success', 'Pelanggaran berhasil dicatat dan poin langsung dihitung.');
    }

    public function show(ViolationCase $case)
    {
        $this->authorize('view', $case);
        $case->load(['student.currentEnrollment.schoolClass', 'academicYear', 'creator.teacher', 'items', 'followUps.creator', 'attachments']);

        return view('app.cases.show', compact('case'));
    }

    public function followUp(Request $r, ViolationCase $case, AuditService $audit)
    {
        $this->authorize('update', $case);
        $d = $r->validate(['type' => 'required|in:'.implode(',', array_column(FollowUpType::cases(), 'value')), 'scheduled_at' => 'nullable|date', 'completed_at' => 'nullable|date', 'parent_name' => 'nullable|string|max:255', 'parent_contact' => 'nullable|string|max:50', 'notes' => 'nullable|string', 'status' => 'required|in:planned,completed,cancelled']);
        $follow = $case->followUps()->create($d + ['created_by' => $r->user()->id]);
        if ($case->status === CaseStatus::Open) {
            $case->update(['status' => CaseStatus::InFollowUp]);
        }$audit->record('follow_up.created', $follow, null, $follow->toArray());

        return back()->with('success', 'Tindak lanjut ditambahkan.');
    }

    public function status(Request $r, ViolationCase $case, AuditService $audit)
    {
        $this->authorize('update', $case);
        $d = $r->validate(['status' => 'required|in:open,in_follow_up,resolved,cancelled', 'reason' => 'required_if:status,cancelled|nullable|string|max:500']);
        $before = $case->toArray();
        $case->update(['status' => $d['status'], 'cancellation_reason' => $d['reason'] ?? null, 'resolved_at' => $d['status'] === 'resolved' ? now() : null]);
        $audit->record('case.status_changed', $case, $before, $case->fresh()->toArray(), $d['reason'] ?? null);

        return back()->with('success', 'Status kasus diperbarui.');
    }

    public function attachment(Request $request, Attachment $attachment)
    {
        $this->authorize('view', $attachment);
        $thumbnail = $request->boolean('thumbnail') && $attachment->thumbnail_path;
        $path = $thumbnail ? $attachment->thumbnail_path : $attachment->path;
        abort_unless(Storage::disk($attachment->disk)->exists($path), 404);
        if ($thumbnail) {
            return Storage::disk($attachment->disk)->response($path, null, ['Content-Type' => $attachment->mime_type, 'Cache-Control' => 'private, max-age=600']);
        }

        return Storage::disk($attachment->disk)->download($path, $attachment->original_name, ['Content-Type' => $attachment->mime_type]);
    }
}
