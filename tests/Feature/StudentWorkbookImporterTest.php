<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AcademicYear;
use App\Models\Enrollment;
use App\Models\ImportBatch;
use App\Models\ImportRow;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentAlias;
use App\Models\User;
use App\Models\ViolationCase;
use App\Services\StudentWorkbookImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class StudentWorkbookImporterTest extends TestCase
{
    use RefreshDatabase;

    public function test_missing_nisn_gets_temporary_identity_and_reimport_is_idempotent(): void
    {
        Storage::fake('local');
        $year = AcademicYear::create(['name' => '2026/2027', 'is_active' => true]);
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('X - 1');
        $sheet->fromArray([['', '', '', ''], ['NO. URUT', 'NISN/NIS', 'NAMA SISWA', 'JK'], [1, null, 'Anandita Zahira', 'P']], null, 'A1');
        $path = tempnam(sys_get_temp_dir(), 'bk-import').'.xlsx';
        (new Xlsx($spreadsheet))->save($path);
        $upload = new UploadedFile($path, 'kelas-x.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
        $importer = app(StudentWorkbookImporter::class);
        $batch = $importer->stage($upload, $year, $user->id);
        $this->assertSame(1, $batch->ready_rows);
        $importer->commit($batch);
        $student = Student::first();
        $this->assertNotNull($student);
        $this->assertStringStartsWith('TMP-2627-X1-', $student->temporary_id);
        $this->assertSame(1, Student::count());
        $same = new UploadedFile($path, 'kelas-x.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
        $this->assertSame($batch->id, $importer->stage($same, $year, $user->id)->id);
    }

    public function test_exported_template_matches_by_system_uuid_without_duplicate(): void
    {
        Storage::fake('local');
        $year = AcademicYear::create(['name' => '2026/2027', 'is_active' => true]);
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $class = SchoolClass::create(['academic_year_id' => $year->id, 'name' => 'XI-SAINS 1', 'grade_level' => 'XI']);
        $student = Student::factory()->create(['nis' => '18713', 'nisn' => '0104377464']);
        Enrollment::create(['student_id' => $student->id, 'academic_year_id' => $year->id, 'class_id' => $class->id, 'status' => 'active']);
        $importer = app(StudentWorkbookImporter::class);
        $path = $importer->template();
        $upload = new UploadedFile($path, 'template-data-siswa.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
        $batch = $importer->stage($upload, $year, $user->id);
        $this->assertSame(1, $batch->ready_rows);
        $this->assertSame($student->id, $batch->rows()->first()->matched_student_id);
        $importer->commit($batch);
        $this->assertSame(1, Student::count());
    }

    public function test_xii_workbook_prefixes_class_grade_and_reference_differences_are_reviewed(): void
    {
        Storage::fake('local');
        $year = AcademicYear::create(['name' => '2026/2027', 'is_active' => true]);
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('SAINS 1');
        $sheet->fromArray([['NO', 'NISN/NIS', 'NAMA SISWA', 'L/P'], [1, '0091234567 / 19001', 'Nama Utama', 'L']], null, 'A1');
        $reference = $spreadsheet->createSheet();
        $reference->setTitle('Sheet1');
        $reference->fromArray([[null, 'Nama Utamaa', 'L', 'Sains 1']], null, 'A1');
        $path = tempnam(sys_get_temp_dir(), 'bk-xii').'.xlsx';
        (new Xlsx($spreadsheet))->save($path);
        $upload = new UploadedFile($path, 'XII GANJIL 20262027.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
        $batch = app(StudentWorkbookImporter::class)->stage($upload, $year, $user->id);
        $this->assertSame(1, $batch->conflict_rows);
        $row = $batch->rows()->where('sheet_name', 'SAINS 1')->first();
        $this->assertSame('XII-SAINS 1', $row->normalized_payload['class_name']);
    }

    public function test_authorized_staff_can_download_conflict_report_but_student_cannot(): void
    {
        Storage::fake('local');
        $year = AcademicYear::create(['name' => '2026/2027', 'is_active' => true]);
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin, 'must_change_password' => false]);
        $batch = ImportBatch::create([
            'uploaded_by' => $admin->id,
            'academic_year_id' => $year->id,
            'original_name' => 'data-uji.xlsx',
            'stored_path' => 'imports/data-uji.xlsx',
            'file_hash' => str_repeat('a', 64),
            'status' => 'review',
            'total_rows' => 1,
            'conflict_rows' => 1,
        ]);
        ImportRow::create([
            'batch_id' => $batch->id,
            'sheet_name' => 'X-1',
            'row_number' => 3,
            'raw_payload' => ['name' => 'Siswa Uji'],
            'normalized_payload' => ['name' => 'Siswa Uji', 'gender' => 'L', 'nisn' => '0012345678', 'nis' => '00001', 'class_name' => 'X-1'],
            'status' => 'conflict',
            'message' => 'Data perlu ditinjau.',
        ]);

        $this->actingAs($admin)
            ->get(route('imports.report', $batch))
            ->assertOk()
            ->assertDownload("laporan-review-impor-{$batch->id}.xlsx");

        $student = Student::factory()->create();
        $studentUser = User::factory()->create([
            'role' => UserRole::Student,
            'student_id' => $student->id,
            'must_change_password' => false,
        ]);
        $this->actingAs($studentUser)->get(route('imports.report', $batch))->assertForbidden();
    }

    public function test_only_super_admin_can_discard_an_uncommitted_review_with_password(): void
    {
        Storage::fake('local');
        $year = AcademicYear::create(['name' => '2026/2027', 'is_active' => true]);
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $coordinator = User::factory()->create(['role' => UserRole::Coordinator]);
        $path = 'imports/review-uji/data.xlsx';
        Storage::disk('local')->put($path, 'file-review');
        $batch = ImportBatch::create([
            'uploaded_by' => $admin->id,
            'academic_year_id' => $year->id,
            'original_name' => 'review-uji.xlsx',
            'stored_path' => $path,
            'file_hash' => str_repeat('b', 64),
            'status' => 'review',
        ]);
        ImportRow::create([
            'batch_id' => $batch->id,
            'sheet_name' => 'X-1',
            'row_number' => 2,
            'raw_payload' => ['name' => 'Siswa Review'],
            'normalized_payload' => ['name' => 'Siswa Review'],
            'status' => 'ready',
        ]);

        $this->actingAs($coordinator)
            ->delete(route('imports.destroy', $batch), ['password' => 'password', 'confirmation' => 'HAPUS'])
            ->assertForbidden();
        $this->assertDatabaseHas('import_batches', ['id' => $batch->id]);

        $this->actingAs($admin)
            ->delete(route('imports.destroy', $batch), ['password' => 'salah', 'confirmation' => 'HAPUS'])
            ->assertSessionHasErrors('password');
        $this->assertDatabaseHas('import_batches', ['id' => $batch->id]);

        $this->actingAs($admin)
            ->delete(route('imports.destroy', $batch), ['password' => 'password', 'confirmation' => 'HAPUS'])
            ->assertRedirect(route('imports.index'));

        $this->assertDatabaseMissing('import_batches', ['id' => $batch->id]);
        $this->assertDatabaseMissing('import_rows', ['batch_id' => $batch->id]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'import.review_discarded', 'subject_id' => $batch->id]);
        Storage::disk('local')->assertMissing($path);

        $replacement = ImportBatch::create([
            'uploaded_by' => $admin->id,
            'academic_year_id' => $year->id,
            'original_name' => 'review-uji-diperbaiki.xlsx',
            'stored_path' => 'imports/review-uji-baru/data.xlsx',
            'file_hash' => str_repeat('b', 64),
            'status' => 'review',
        ]);
        $this->assertNotNull($replacement);
    }

    public function test_committed_import_cannot_be_discarded(): void
    {
        Storage::fake('local');
        $year = AcademicYear::create(['name' => '2026/2027', 'is_active' => true]);
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $batch = ImportBatch::create([
            'uploaded_by' => $admin->id,
            'academic_year_id' => $year->id,
            'original_name' => 'resmi.xlsx',
            'stored_path' => 'imports/resmi/data.xlsx',
            'file_hash' => str_repeat('c', 64),
            'status' => 'committed',
            'committed_at' => now(),
        ]);

        $this->actingAs($admin)
            ->delete(route('imports.destroy', $batch), ['password' => 'password', 'confirmation' => 'HAPUS'])
            ->assertStatus(422);

        $this->assertDatabaseHas('import_batches', ['id' => $batch->id, 'status' => 'committed']);
    }

    public function test_reimport_updates_the_same_student_and_preserves_violation_history(): void
    {
        Storage::fake('local');
        $year = AcademicYear::create(['name' => '2026/2027', 'is_active' => true]);
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $student = Student::factory()->create(['name' => 'Nama Lama', 'normalized_name' => 'NAMA LAMA', 'gender' => 'L']);
        $case = ViolationCase::create([
            'case_number' => 'BK-2026-00999',
            'student_id' => $student->id,
            'academic_year_id' => $year->id,
            'created_by' => $admin->id,
            'occurred_at' => now(),
            'chronology' => 'Riwayat yang harus tetap tersimpan setelah pembaruan data siswa.',
            'status' => 'open',
        ]);

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('XI-2');
        $sheet->fromArray([
            ['ID SISTEM', 'NAMA SISWA', 'JK', 'KELAS'],
            [$student->id, 'Nama Baru Lengkap', 'L', 'XI-2'],
        ], null, 'A1');
        $path = tempnam(sys_get_temp_dir(), 'bk-update').'.xlsx';
        (new Xlsx($spreadsheet))->save($path);
        $upload = new UploadedFile($path, 'perbaikan-data-siswa.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
        $importer = app(StudentWorkbookImporter::class);
        $batch = $importer->stage($upload, $year, $admin->id);
        $importer->commit($batch);

        $this->assertSame(1, Student::count());
        $this->assertSame('Nama Baru Lengkap', $student->fresh()->name);
        $this->assertDatabaseHas('student_aliases', ['student_id' => $student->id, 'name' => 'Nama Lama']);
        $this->assertSame('XI-2', $student->fresh()->currentEnrollment->schoolClass->name);
        $this->assertDatabaseHas('violation_cases', ['id' => $case->id, 'student_id' => $student->id]);
        $this->assertSame($student->id, $case->fresh()->student->id);
        $this->assertSame(1, StudentAlias::count());
    }

    public function test_reimport_without_identifiers_reviews_and_updates_one_exact_name_candidate(): void
    {
        Storage::fake('local');
        $year = AcademicYear::create(['name' => '2026/2027', 'is_active' => true]);
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $student = Student::factory()->create([
            'name' => 'Anandita Zahira',
            'normalized_name' => 'ANANDITA ZAHIRA',
            'gender' => 'P',
            'nis' => null,
            'nisn' => null,
        ]);
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('X-3');
        $sheet->fromArray([
            ['NO', 'NAMA SISWA', 'JK'],
            [1, 'Anandita Zahira', 'P'],
        ], null, 'A1');
        $path = tempnam(sys_get_temp_dir(), 'bk-name-review').'.xlsx';
        (new Xlsx($spreadsheet))->save($path);
        $upload = new UploadedFile($path, 'kelas-x-diperbaiki.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
        $importer = app(StudentWorkbookImporter::class);
        $batch = $importer->stage($upload, $year, $admin->id);
        $row = $batch->rows()->firstOrFail();

        $this->assertSame('conflict', $row->status->value);
        $this->assertSame($student->id, $row->matched_student_id);
        $this->actingAs($admin)
            ->get(route('imports.show', $batch))
            ->assertOk()
            ->assertSee('Calon siswa yang sudah ada')
            ->assertSee('Perbarui siswa ini');

        $importer->resolve($batch, $row, 'accept');
        $importer->commit($batch->fresh());

        $this->assertSame(1, Student::count());
        $this->assertSame('X-3', $student->fresh()->currentEnrollment->schoolClass->name);
    }
}
