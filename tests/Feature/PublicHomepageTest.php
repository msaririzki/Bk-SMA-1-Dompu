<?php

namespace Tests\Feature;

use App\Enums\CaseStatus;
use App\Enums\StudentStatus;
use App\Enums\UserRole;
use App\Models\AcademicYear;
use App\Models\CaseItem;
use App\Models\SeverityLevel;
use App\Models\Student;
use App\Models\User;
use App\Models\ViolationCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicHomepageTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_preview_uses_current_database_totals(): void
    {
        $year = AcademicYear::create(['name' => '2026/2027', 'is_active' => true]);
        $user = User::factory()->create(['role' => UserRole::Coordinator]);
        $students = Student::factory()->count(3)->create();
        $graduated = Student::factory()->create(['status' => StudentStatus::Graduated]);

        SeverityLevel::create(['name' => 'Ringan', 'min_points' => 1, 'max_points' => 24, 'sort_order' => 1]);
        SeverityLevel::create(['name' => 'Sedang', 'min_points' => 25, 'max_points' => 49, 'sort_order' => 2]);
        SeverityLevel::create(['name' => 'Berat', 'min_points' => 50, 'max_points' => null, 'sort_order' => 3]);

        $this->createCase($students[0], $year, $user, 'BK-2026-00001', 10, CaseStatus::Open);
        $this->createCase($students[1], $year, $user, 'BK-2026-00002', 30, CaseStatus::InFollowUp);
        $this->createCase($students[2], $year, $user, 'BK-2026-00003', 60, CaseStatus::Resolved);
        $this->createCase($graduated, $year, $user, 'BK-2026-00004', 100, CaseStatus::Cancelled);

        $response = $this->get(route('home'))->assertOk();
        $preview = $response->viewData('preview');

        $this->assertSame(3, $preview['students']);
        $this->assertSame(4, $preview['month_cases']);
        $this->assertSame(2, $preview['open_cases']);
        $this->assertSame(
            [
                ['label' => 'Ringan', 'count' => 1, 'percentage' => 33, 'color' => 'bg-teal-400'],
                ['label' => 'Sedang', 'count' => 1, 'percentage' => 33, 'color' => 'bg-amber-400'],
                ['label' => 'Berat', 'count' => 1, 'percentage' => 33, 'color' => 'bg-orange-400'],
            ],
            $preview['priorities']->all(),
        );

        $response
            ->assertSee('Sistem informasi BK untuk pendampingan siswa SMAN 1 Dompu.')
            ->assertSee('Cek pelanggaran siswa')
            ->assertSee('Lihat riwayat dan progres pelanggaran siswa secara mudah.');
    }

    private function createCase(
        Student $student,
        AcademicYear $year,
        User $user,
        string $number,
        int $points,
        CaseStatus $status,
    ): void {
        $case = ViolationCase::create([
            'case_number' => $number,
            'student_id' => $student->id,
            'academic_year_id' => $year->id,
            'created_by' => $user->id,
            'occurred_at' => now(),
            'chronology' => 'Data untuk menguji ringkasan beranda sekolah.',
            'status' => $status,
        ]);

        CaseItem::create([
            'case_id' => $case->id,
            'instrument_code' => 'TEST',
            'instrument_name' => 'Instrumen pengujian',
            'points' => $points,
        ]);
    }
}
