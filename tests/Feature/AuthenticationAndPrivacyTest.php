<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\User;
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
}
