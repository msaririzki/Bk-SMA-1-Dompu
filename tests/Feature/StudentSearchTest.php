<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AcademicYear;
use App\Models\Enrollment;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_coordinator_can_search_active_students_by_partial_name_and_identifier(): void
    {
        [$year, $class] = $this->schoolContext();
        $student = Student::factory()->create([
            'name' => 'Muhammad Rizky Ramadhan',
            'normalized_name' => 'MUHAMMAD RIZKY RAMADHAN',
            'nisn' => '0109647921',
        ]);
        Enrollment::create(['student_id' => $student->id, 'academic_year_id' => $year->id, 'class_id' => $class->id]);
        $inactive = Student::factory()->create([
            'name' => 'Muhammad Rizky Alumni',
            'normalized_name' => 'MUHAMMAD RIZKY ALUMNI',
            'status' => 'graduated',
        ]);
        $user = User::factory()->create(['role' => UserRole::Coordinator]);

        $this->actingAs($user)
            ->getJson(route('students.search', ['q' => 'rizky']))
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertJsonFragment([
                'id' => $student->id,
                'name' => 'Muhammad Rizky Ramadhan',
                'class_name' => 'X-1',
                'identifier_label' => 'NISN',
                'identifier' => '0109647921',
            ])
            ->assertJsonMissing(['id' => $inactive->id]);

        $this->actingAs($user)
            ->getJson(route('students.search', ['q' => '6479']))
            ->assertOk()
            ->assertJsonFragment(['id' => $student->id]);
    }

    public function test_student_search_requires_two_characters_and_is_not_available_to_student_accounts(): void
    {
        $staff = User::factory()->create(['role' => UserRole::Coordinator]);

        $this->actingAs($staff)
            ->getJson(route('students.search', ['q' => 'M']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('q');

        $studentAccount = User::factory()->create(['role' => UserRole::Student]);
        $this->actingAs($studentAccount)
            ->getJson(route('students.search', ['q' => 'Mu']))
            ->assertForbidden();
    }

    public function test_all_student_forms_use_the_partial_search_component(): void
    {
        $user = User::factory()->create(['role' => UserRole::Coordinator]);

        foreach ([route('cases.create'), route('documents.create'), route('home-visits.create')] as $url) {
            $this->actingAs($user)
                ->get($url)
                ->assertOk()
                ->assertSee('data-student-autocomplete', false)
                ->assertSee(route('students.search'), false);
        }
    }

    private function schoolContext(): array
    {
        $year = AcademicYear::create(['name' => '2026/2027', 'is_active' => true]);
        $class = SchoolClass::create(['academic_year_id' => $year->id, 'name' => 'X-1', 'grade_level' => 'X']);

        return [$year, $class];
    }
}
