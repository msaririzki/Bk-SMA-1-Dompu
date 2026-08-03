<?php

namespace Tests\Feature;

use App\Enums\DocumentType;
use App\Enums\StudentStatus;
use App\Enums\UserRole;
use App\Models\AcademicYear;
use App\Models\Enrollment;
use App\Models\HomeVisit;
use App\Models\SchoolClass;
use App\Models\SchoolDocument;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentPdfTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_visit_pdf_is_rendered_as_a4_pdf(): void
    {
        $year = AcademicYear::create(['name' => '2026/2027', 'is_active' => true]);
        $class = SchoolClass::create(['academic_year_id' => $year->id, 'name' => 'XI-SAINS 1', 'grade_level' => 'XI']);
        $student = Student::factory()->create(['name' => 'Siswa Contoh', 'status' => StudentStatus::Active]);
        Enrollment::create(['student_id' => $student->id, 'academic_year_id' => $year->id, 'class_id' => $class->id, 'status' => 'active']);
        $user = User::factory()->create(['role' => UserRole::Coordinator]);
        $document = SchoolDocument::create(['student_id' => $student->id, 'academic_year_id' => $year->id, 'created_by' => $user->id, 'type' => DocumentType::HomeVisit, 'document_date' => '2026-08-03', 'status' => 'final', 'payload' => []]);
        HomeVisit::create(['document_id' => $document->id, 'counselee_name' => $student->name, 'class_name' => $class->name, 'gender' => 'L', 'address' => 'Dompu', 'parent_name' => 'Orang Tua Contoh', 'problem' => 'Kehadiran siswa membutuhkan pendampingan bersama keluarga.', 'purpose' => 'Menyepakati dukungan antara sekolah dan keluarga.', 'visit_date' => '2026-08-03', 'met_with' => 'Ayah siswa', 'result' => 'Orang tua memahami kondisi dan bersedia mendampingi siswa.', 'follow_up' => 'Pemantauan kehadiran selama empat minggu.', 'counselor_name' => 'Guru BK', 'homeroom_name' => 'Wali Kelas', 'coordinator_name' => 'Koordinator BK', 'place' => 'Dompu']);
        $response = $this->actingAs($user)->get(route('documents.pdf', $document));
        $response->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }
}
