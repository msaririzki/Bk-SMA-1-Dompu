<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\User;
use App\Models\ViolationCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationAndPrivacyTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_login_with_username(): void
    {
        $user = User::factory()->create(['username' => 'guru.bk', 'password' => Hash::make('rahasia123'), 'role' => UserRole::Counselor]);
        $this->post('/login', ['username' => 'guru.bk', 'password' => 'rahasia123'])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_student_cannot_open_internal_app(): void
    {
        $student = Student::factory()->create();
        $user = User::factory()->create(['role' => UserRole::Student, 'student_id' => $student->id]);
        $this->actingAs($user)->get('/app')->assertForbidden();
    }

    public function test_student_portal_only_uses_attached_student(): void
    {
        $year = AcademicYear::create(['name' => '2026/2027', 'is_active' => true]);
        $student = Student::factory()->create();
        $other = Student::factory()->create();
        $user = User::factory()->create(['role' => UserRole::Student, 'student_id' => $student->id]);
        $response = $this->actingAs($user)->get('/siswa');
        $response->assertOk()->assertSee($student->name)->assertDontSee($other->name);
    }

    public function test_super_admin_dashboard_renders_without_activity_data(): void
    {
        AcademicYear::create(['name' => '2026/2027', 'is_active' => true]);
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $this->actingAs($user)->get(route('dashboard'))->assertOk()->assertSee('Dashboard BK');
    }

    public function test_dashboard_renders_aggregated_points_when_cases_exist(): void
    {
        $year = AcademicYear::create(['name' => '2026/2027', 'is_active' => true]);
        $user = User::factory()->create(['role' => UserRole::Coordinator]);
        $student = Student::factory()->create(['name' => 'Siswa Prioritas Uji']);
        $case = ViolationCase::create([
            'case_number' => 'UAT-AGG-001',
            'student_id' => $student->id,
            'academic_year_id' => $year->id,
            'created_by' => $user->id,
            'occurred_at' => now(),
            'chronology' => 'Kasus uji agregasi dashboard.',
            'status' => 'open',
        ]);
        $case->items()->create([
            'instrument_code' => 'UAT01',
            'instrument_name' => 'Instrumen UAT',
            'points' => 50,
            'sanction_snapshot' => 'Pembinaan UAT',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Siswa Prioritas Uji')
            ->assertSee('50');
    }
}
