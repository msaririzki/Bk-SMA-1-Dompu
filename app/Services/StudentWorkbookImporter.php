<?php

namespace App\Services;

use App\Enums\ImportRowStatus;
use App\Enums\StudentStatus;
use App\Models\AcademicYear;
use App\Models\Enrollment;
use App\Models\ImportBatch;
use App\Models\ImportRow;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class StudentWorkbookImporter
{
    private const IGNORED_SHEETS = ['GABUNG', 'DATA AWAL', 'URUT PEMINATAN', 'SHEET1'];

    public function __construct(private readonly StudentIdentityService $identity) {}

    public function stage(UploadedFile $file, AcademicYear $year, int $userId): ImportBatch
    {
        $hash = hash_file('sha256', $file->getRealPath());
        if ($existing = ImportBatch::where('academic_year_id', $year->id)->where('file_hash', $hash)->first()) {
            return $existing;
        }

        $id = (string) Str::uuid();
        $path = $file->storeAs("imports/{$id}", Str::uuid().'.xlsx', 'local');
        $batch = ImportBatch::create([
            'id' => $id, 'uploaded_by' => $userId, 'academic_year_id' => $year->id,
            'original_name' => $file->getClientOriginalName(), 'stored_path' => $path,
            'file_hash' => $hash, 'status' => 'review',
        ]);

        $spreadsheet = IOFactory::load(Storage::disk('local')->path($path));
        foreach ($spreadsheet->getWorksheetIterator() as $sheet) {
            if (in_array(Str::upper(trim($sheet->getTitle())), self::IGNORED_SHEETS, true)) {
                continue;
            }
            $header = $this->findHeaderRow($sheet->toArray(null, true, true, false));
            if ($header === null) {
                continue;
            }
            $rows = $sheet->toArray(null, true, true, false);
            $columns = $this->detectColumns($rows[$header]);
            for ($index = $header + 1; $index < count($rows); $index++) {
                $row = $rows[$index];
                $number = $this->cell($row, $columns, 'number');
                $rawId = $this->cell($row, $columns, 'identifier');
                $name = trim((string) $this->cell($row, $columns, 'name'));
                $gender = Str::upper(trim((string) $this->cell($row, $columns, 'gender')));
                if ($name === '' || ! in_array($gender, ['L', 'P'], true)) {
                    continue;
                }
                $ids = $this->identity->parseCombinedIdentifier($rawId);
                $separateNisn = $this->digits($this->cell($row, $columns, 'nisn'));
                $separateNis = $this->digits($this->cell($row, $columns, 'nis'));
                $rawClass = trim((string) $this->cell($row, $columns, 'class'));
                $normalized = ['system_id' => trim((string) $this->cell($row, $columns, 'system_id')) ?: null, 'nisn' => $separateNisn ?: $ids['nisn'], 'nis' => $separateNis ?: $ids['nis'], 'name' => preg_replace('/\s+/u', ' ', $name), 'normalized_name' => $this->identity->normalizeName($name), 'gender' => $gender, 'class_name' => $this->normalizeClassName($rawClass ?: $sheet->getTitle(), $file->getClientOriginalName()), 'roll_number' => is_numeric($number) ? (int) $number : null];
                [$status, $match, $message] = $this->match($normalized, $year->id);
                ImportRow::create(['batch_id' => $batch->id, 'sheet_name' => $sheet->getTitle(), 'row_number' => $index + 1, 'raw_payload' => ['number' => $number, 'identifier' => $rawId, 'nisn' => $separateNisn, 'nis' => $separateNis, 'name' => $name, 'gender' => $gender, 'class' => $rawClass], 'normalized_payload' => $normalized, 'status' => $status, 'matched_student_id' => $match?->id, 'message' => $message]);
            }
        }
        $this->stageReferenceConflicts($spreadsheet, $batch, $file->getClientOriginalName());
        $this->refreshCounts($batch);

        return $batch->fresh();
    }

    private function findHeaderRow(array $rows): ?int
    {
        foreach (array_slice($rows, 0, 15, true) as $index => $row) {
            $joined = Str::upper(implode(' ', array_map(fn ($v) => (string) $v, $row)));
            if (str_contains($joined, 'NAMA') && (str_contains($joined, 'JK') || str_contains($joined, 'L/P'))) {
                return $index;
            }
        }

        return null;
    }

    private function detectColumns(array $header): array
    {
        $map = [];
        foreach ($header as $index => $value) {
            $label = Str::upper(trim(preg_replace('/\s+/u', ' ', (string) $value)));
            $compact = preg_replace('/[^A-Z0-9\/]+/', '', $label);
            if (str_contains($label, 'ID SISTEM')) {
                $map['system_id'] = $index;
            } elseif (str_contains($compact, 'NISN/NIS') || str_contains($compact, 'NIS/NISN')) {
                $map['identifier'] = $index;
            } elseif ($compact === 'NISN') {
                $map['nisn'] = $index;
            } elseif ($compact === 'NIS') {
                $map['nis'] = $index;
            } elseif (str_contains($label, 'NAMA') && ! str_contains($label, 'WALI')) {
                $map['name'] = $index;
            } elseif (in_array($compact, ['JK', 'L/P', 'LP'], true)) {
                $map['gender'] = $index;
            } elseif (str_contains($label, 'KELAS')) {
                $map['class'] = $index;
            } elseif (str_starts_with($compact, 'NO')) {
                $map['number'] = $index;
            }
        }

        return $map;
    }

    private function cell(array $row, array $columns, string $key): mixed
    {
        return isset($columns[$key]) ? ($row[$columns[$key]] ?? null) : null;
    }

    private function digits(mixed $value): ?string
    {
        $digits = preg_replace('/\D/', '', trim((string) $value));

        return $digits !== '' ? $digits : null;
    }

    private function match(array $data, int $yearId): array
    {
        $match = null;
        if ($data['system_id']) {
            $match = Student::find($data['system_id']);
        }
        if (! $match && $data['nisn']) {
            $match = Student::where('nisn', $data['nisn'])->first();
        }
        if (! $match && $data['nis']) {
            $match = Student::where('nis', $data['nis'])->first();
        }
        if ($match) {
            return [ImportRowStatus::Update, $match, 'Identitas resmi cocok; enrollment akan diperbarui.'];
        }
        $candidates = Student::where('normalized_name', $data['normalized_name'])->where('gender', $data['gender'])->count();
        if ($candidates > 0) {
            return [ImportRowStatus::Conflict, null, 'Nama serupa ditemukan. Tinjau manual; sistem tidak menggabungkan berdasarkan nama.'];
        }

        return [ImportRowStatus::Ready, null, $data['nisn'] || $data['nis'] ? 'Siswa baru dengan identitas resmi.' : 'Siswa baru; ID sementara akan dibuat.'];
    }

    public function commit(ImportBatch $batch): int
    {
        abort_if($batch->status === 'committed', 422, 'Batch sudah pernah dikomit.');
        $count = DB::transaction(function () use ($batch) {
            $year = AcademicYear::whereKey($batch->academic_year_id)->lockForUpdate()->firstOrFail();
            $count = 0;
            foreach ($batch->rows()->whereIn('status', [ImportRowStatus::Ready->value, ImportRowStatus::Update->value])->get() as $row) {
                $data = $row->normalized_payload;
                $class = $this->resolveClass($year, $data['class_name']);
                $student = $row->matchedStudent;
                if (! $student) {
                    $student = Student::create([
                        'temporary_id' => $this->identity->nextTemporaryId($year->name, $class->name),
                        'nis' => $data['nis'], 'nisn' => $data['nisn'], 'name' => $data['name'], 'normalized_name' => $data['normalized_name'], 'gender' => $data['gender'], 'status' => StudentStatus::Active,
                    ]);
                } else {
                    $student->update(array_filter(['nis' => $data['nis'], 'nisn' => $data['nisn'], 'name' => $data['name'], 'normalized_name' => $data['normalized_name'], 'gender' => $data['gender']], fn ($v) => $v !== null));
                }
                Enrollment::updateOrCreate(['student_id' => $student->id, 'academic_year_id' => $year->id], ['class_id' => $class->id, 'roll_number' => $data['roll_number'], 'status' => 'active']);
                $row->update(['status' => ImportRowStatus::Imported, 'matched_student_id' => $student->id, 'message' => 'Berhasil diimpor.']);
                $count++;
            }
            $batch->update(['status' => 'committed', 'imported_rows' => $count, 'committed_at' => now()]);

            return $count;
        });
        $this->refreshCounts($batch);

        return $count;
    }

    public function resolve(ImportBatch $batch, ImportRow $row, string $decision): void
    {
        abort_unless($row->batch_id === $batch->id, 404);
        abort_if($batch->status === 'committed', 422, 'Batch sudah dikomit.');
        abort_unless($row->status === ImportRowStatus::Conflict, 422, 'Baris ini bukan konflik aktif.');
        $referenceOnly = (bool) ($row->normalized_payload['reference_only'] ?? false);
        if ($decision === 'accept') {
            abort_if($referenceOnly, 422, 'Baris referensi tidak dapat diimpor langsung. Perbaiki workbook utama bila siswa memang harus ditambahkan.');
            $row->update(['status' => $row->matched_student_id ? ImportRowStatus::Update : ImportRowStatus::Ready, 'message' => 'Disetujui manual berdasarkan sheet kelas utama.']);
        } elseif ($decision === 'ignore') {
            $row->update(['status' => ImportRowStatus::Ignored, 'message' => 'Diabaikan setelah review manual.']);
        } else {
            abort(422, 'Keputusan review tidak dikenal.');
        }
        $this->refreshCounts($batch);
    }

    private function stageReferenceConflicts(Spreadsheet $spreadsheet, ImportBatch $batch, string $workbookName): void
    {
        if ($sheet = $spreadsheet->getSheetByName('DATA AWAL')) {
            $this->compareOfficialReference($sheet, $batch);
        }
        if ($sheet = $spreadsheet->getSheetByName('GABUNG')) {
            $this->compareNameReference($sheet, $batch, $workbookName, true);
        }
        if ($sheet = $spreadsheet->getSheetByName('Sheet1')) {
            $this->compareNameReference($sheet, $batch, $workbookName, false);
        }
    }

    private function compareOfficialReference($sheet, ImportBatch $batch): void
    {
        $rows = $sheet->toArray(null, true, true, false);
        $header = $this->findHeaderRow($rows);
        if ($header === null) {
            return;
        }
        $primary = $batch->rows()->get();
        $seen = [];
        for ($index = $header + 1; $index < count($rows); $index++) {
            $row = $rows[$index];
            if (! is_numeric($row[0] ?? null)) {
                continue;
            }$name = trim((string) ($row[3] ?? ''));
            $gender = Str::upper(trim((string) ($row[4] ?? '')));
            if ($name === '' || ! in_array($gender, ['L', 'P'], true)) {
                continue;
            }
            $ids = $this->identity->parseCombinedIdentifier($row[2] ?? null);
            $match = $primary->first(fn ($item) => ($ids['nisn'] && $item->normalized_payload['nisn'] === $ids['nisn']) || ($ids['nis'] && $item->normalized_payload['nis'] === $ids['nis']));
            if ($match) {
                $seen[$match->id] = true;
                if ($match->normalized_payload['normalized_name'] !== $this->identity->normalizeName($name)) {
                    $match->update(['status' => ImportRowStatus::Conflict, 'message' => "Ejaan nama berbeda dengan DATA AWAL: {$name}. Tinjau manual."]);
                }

                continue;
            }
            ImportRow::create(['batch_id' => $batch->id, 'sheet_name' => $sheet->getTitle(), 'row_number' => $index + 1, 'raw_payload' => ['identifier' => $row[2] ?? null, 'name' => $name, 'gender' => $gender, 'reference' => true], 'normalized_payload' => ['nisn' => $ids['nisn'], 'nis' => $ids['nis'], 'name' => $name, 'normalized_name' => $this->identity->normalizeName($name), 'gender' => $gender, 'class_name' => $row[1] ?? null, 'reference_only' => true], 'status' => ImportRowStatus::Conflict, 'message' => 'Ada pada DATA AWAL tetapi tidak ada pada sheet kelas. Tinjau status siswa.']);
        }
        foreach ($primary as $item) {
            $data = $item->normalized_payload;
            if (($data['nisn'] || $data['nis']) && ! isset($seen[$item->id])) {
                $item->update(['status' => ImportRowStatus::Conflict, 'message' => 'Ada pada sheet kelas tetapi tidak ada pada DATA AWAL. Tinjau sebagai siswa baru/perpindahan.']);
            }
        }
    }

    private function compareNameReference($sheet, ImportBatch $batch, string $workbookName, bool $hasHeader): void
    {
        $rows = $sheet->toArray(null, true, true, false);
        $start = $hasHeader ? 1 : 0;
        $primary = $batch->rows()->where('sheet_name', '!=', $sheet->getTitle())->get();
        $matched = [];
        for ($index = $start; $index < count($rows); $index++) {
            $row = $rows[$index];
            $name = trim((string) ($row[1] ?? ''));
            $gender = Str::upper(trim((string) ($row[2] ?? '')));
            $rawClass = trim((string) ($row[3] ?? ''));
            if ($name === '' || ! in_array($gender, ['L', 'P'], true) || $rawClass === '') {
                continue;
            }
            $className = $this->normalizeClassName($rawClass, $workbookName);
            $normalized = $this->identity->normalizeName($name);
            $candidates = $primary->filter(fn ($item) => ! isset($matched[$item->id]) && $item->normalized_payload['gender'] === $gender);
            $match = $candidates->first(fn ($item) => $item->normalized_payload['normalized_name'] === $normalized && $item->normalized_payload['class_name'] === $className);
            if (! $match) {
                $sameName = $candidates->first(fn ($item) => $item->normalized_payload['normalized_name'] === $normalized);
                if ($sameName) {
                    $sameName->update(['status' => ImportRowStatus::Conflict, 'message' => "Kelas berbeda dengan {$sheet->getTitle()} ({$rawClass}). Tinjau manual."]);
                    $matched[$sameName->id] = true;

                    continue;
                }
            }
            if (! $match) {
                $match = $candidates->filter(fn ($item) => $item->normalized_payload['class_name'] === $className)->sortBy(fn ($item) => levenshtein($normalized, $item->normalized_payload['normalized_name']))->first();
                if ($match && levenshtein($normalized, $match->normalized_payload['normalized_name']) <= 2) {
                    $match->update(['status' => ImportRowStatus::Conflict, 'message' => "Kemungkinan ejaan berbeda di {$sheet->getTitle()}: {$name}. Tinjau manual."]);
                    $matched[$match->id] = true;

                    continue;
                }
            }
            if ($match) {
                $matched[$match->id] = true;

                continue;
            }
            ImportRow::create(['batch_id' => $batch->id, 'sheet_name' => $sheet->getTitle(), 'row_number' => $index + 1, 'raw_payload' => ['name' => $name, 'gender' => $gender, 'class' => $rawClass, 'reference' => true], 'normalized_payload' => ['nisn' => null, 'nis' => null, 'name' => $name, 'normalized_name' => $normalized, 'gender' => $gender, 'class_name' => $className, 'reference_only' => true], 'status' => ImportRowStatus::Conflict, 'message' => "Ada pada {$sheet->getTitle()} tetapi tidak ada pada sheet kelas. Tinjau manual."]);
        }
        foreach ($primary as $item) {
            if (! isset($matched[$item->id])) {
                $item->update(['status' => ImportRowStatus::Conflict, 'message' => "Ada pada sheet kelas tetapi tidak ditemukan pada {$sheet->getTitle()}. Tinjau manual."]);
            }
        }
    }

    private function normalizeClassName(string $name, ?string $workbookName = null): string
    {
        $name = Str::upper(trim(preg_replace('/\s+/', ' ', str_replace(['–', '—'], '-', $name))));
        $name = preg_replace('/^X1(?=[-\s])/', 'XI', $name);
        if ($workbookName && preg_match('/^XII\b/i', basename($workbookName)) && ! str_starts_with($name, 'XII')) {
            $name = 'XII-'.$name;
        }
        if ($workbookName && preg_match('/^X\b/i', basename($workbookName)) && preg_match('/^\d+$/', $name)) {
            $name = 'X-'.$name;
        }

        return preg_replace('/\s*-\s*/', '-', $name);
    }

    private function resolveClass(AcademicYear $year, string $name): SchoolClass
    {
        preg_match('/^(XII|XI|X)/', $name, $grade);
        preg_match('/(SAINS|SOSHUM)/', $name, $track);
        preg_match('/(\d+)$/', $name, $group);

        return SchoolClass::firstOrCreate(['academic_year_id' => $year->id, 'name' => $name], ['grade_level' => $grade[1] ?? 'X', 'track' => $track[1] ?? null, 'group_number' => isset($group[1]) ? (int) $group[1] : null]);
    }

    private function refreshCounts(ImportBatch $batch): void
    {
        $batch->update(['total_rows' => $batch->rows()->count(), 'ready_rows' => $batch->rows()->whereIn('status', [ImportRowStatus::Ready->value, ImportRowStatus::Update->value])->count(), 'conflict_rows' => $batch->rows()->where('status', ImportRowStatus::Conflict->value)->count(), 'invalid_rows' => $batch->rows()->where('status', ImportRowStatus::Invalid->value)->count()]);
    }

    public function template(): string
    {
        $sheet = (new Spreadsheet)->getActiveSheet();
        $sheet->setTitle('Data Siswa');
        $sheet->fromArray([['ID SISTEM', 'NIS', 'NISN', 'NAMA LENGKAP', 'L/P', 'KELAS', 'TAHUN PELAJARAN', 'STATUS']], null, 'A1');
        $year = AcademicYear::active();
        $row = 2;
        if ($year) {
            foreach (Student::with(['enrollments' => fn ($q) => $q->where('academic_year_id', $year->id)->with('schoolClass')])->orderBy('name')->get() as $student) {
                $enrollment = $student->enrollments->first();
                $sheet->fromArray([[$student->id, $student->nis, $student->nisn, $student->name, $student->gender, $enrollment?->schoolClass?->name, $year->name, $student->status->value]], null, 'A'.$row++);
            }
        }
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);
        $sheet->freezePane('A2');
        $sheet->setAutoFilter('A1:H1');
        $sheet->getStyle('A2:C'.max(2, $row - 1))->getNumberFormat()->setFormatCode('@');
        foreach (range('A', 'H') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        $path = storage_path('app/private/templates/template-data-siswa.xlsx');
        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0775, true);
        }
        (new Xlsx($sheet->getParent()))->save($path);

        return $path;
    }
}
