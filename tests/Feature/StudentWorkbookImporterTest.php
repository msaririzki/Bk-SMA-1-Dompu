<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AcademicYear;
use App\Models\Enrollment;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
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
        $row = $batch->rows()->where('sheet_name','SAINS 1')->first();
        $this->assertSame('XII-SAINS 1',$row->normalized_payload['class_name']);
    }
}
