<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\ImportBatch;
use App\Models\ImportRow;
use App\Services\AuditService;
use App\Services\StudentWorkbookImporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class StudentImportController extends Controller
{
    public function index()
    {
        return view('app.imports.index', ['years' => AcademicYear::orderByDesc('name')->get(), 'batches' => ImportBatch::latest()->paginate(15)]);
    }

    public function store(Request $r, StudentWorkbookImporter $importer)
    {
        $d = $r->validate(['academic_year_id' => 'required|exists:academic_years,id', 'file' => 'required|file|mimes:xlsx|max:25600']);
        $batch = $importer->stage($r->file('file'), AcademicYear::findOrFail($d['academic_year_id']), $r->user()->id);

        return redirect()->route('imports.show', $batch)->with('success', 'File diproses. Periksa hasil sebelum mengimpor.');
    }

    public function show(Request $r, ImportBatch $batch)
    {
        $rows = $batch->rows()->with('matchedStudent')->when($r->status, fn ($q, $status) => $q->where('status', $status))->when($r->sheet, fn ($q, $sheet) => $q->where('sheet_name', $sheet))->when($r->q, fn ($q, $term) => $q->where('raw_payload', 'like', "%{$term}%"))->orderByRaw("CASE WHEN status = 'conflict' THEN 0 WHEN status = 'invalid' THEN 1 ELSE 2 END")->orderBy('sheet_name')->orderBy('row_number')->paginate(50)->withQueryString();
        $sheets = $batch->rows()->distinct()->orderBy('sheet_name')->pluck('sheet_name');

        return view('app.imports.show', compact('batch', 'rows', 'sheets'));
    }

    public function commit(ImportBatch $batch, StudentWorkbookImporter $importer, AuditService $audit)
    {
        $count = $importer->commit($batch);
        $audit->record('import.committed', $batch, null, $batch->fresh()->toArray());

        return back()->with('success', "{$count} data siswa berhasil diimpor.");
    }

    public function resolve(Request $r, ImportBatch $batch, ImportRow $row, StudentWorkbookImporter $importer, AuditService $audit)
    {
        $d = $r->validate(['decision' => 'required|in:accept,ignore']);
        $before = $row->toArray();
        $importer->resolve($batch, $row, $d['decision']);
        $audit->record('import.row_resolved', $row, $before, $row->fresh()->toArray(), $d['decision']);

        return back()->with('success', 'Konflik baris telah ditinjau.');
    }

    public function template(StudentWorkbookImporter $importer)
    {
        return response()->download($importer->template(), 'template-data-siswa.xlsx');
    }

    public function report(ImportBatch $batch, StudentWorkbookImporter $importer)
    {
        return response()
            ->download($importer->reviewReport($batch), "laporan-review-impor-{$batch->id}.xlsx")
            ->deleteFileAfterSend(true);
    }

    public function destroy(Request $request, ImportBatch $batch, AuditService $audit)
    {
        abort_unless($batch->status === 'review', 422, 'Hanya file yang masih dalam tahap review yang dapat dibatalkan. Data impor yang sudah dikonfirmasi tidak dapat dihapus.');

        $request->validate([
            'password' => ['required', 'current_password'],
            'confirmation' => ['required', 'in:HAPUS'],
        ], [
            'password.current_password' => 'Kata sandi akun tidak sesuai.',
            'confirmation.in' => 'Ketik HAPUS untuk mengonfirmasi pembatalan file review.',
        ], [
            'password' => 'kata sandi akun',
            'confirmation' => 'konfirmasi',
        ]);

        $before = $batch->toArray();
        $storedPath = $batch->stored_path;

        DB::transaction(function () use ($audit, $batch, $before): void {
            $audit->record('import.review_discarded', $batch, $before, null, 'File tahap review dibatalkan oleh Super Admin.');
            $batch->delete();
        });

        Storage::disk('local')->delete($storedPath);

        return redirect()->route('imports.index')->with('success', 'File review berhasil dibatalkan. Tidak ada data siswa atau riwayat pelanggaran yang dihapus.');
    }
}
