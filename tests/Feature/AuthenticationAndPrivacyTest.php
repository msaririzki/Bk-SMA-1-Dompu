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

    public function test_student_can_login_with_nisn_without_pin(): void
    {
        $student = Student::factory()->create(['nisn' => '0012345678']);

        $this->post(route('student.login.store'), ['identifier' => '0012345678'])
            ->assertRedirect(route('student.portal'));

        $user = User::where('student_id', $student->id)->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertFalse($user->must_change_password);
        $this->assertDatabaseHas('audit_logs', ['action' => 'student_account.auto_created', 'subject_id' => (string) $user->id]);
    }

    public function test_student_can_login_with_nis_without_pin(): void
    {
        $student = Student::factory()->create(['nis' => '12345678', 'nisn' => null]);
        $user = User::factory()->create(['role' => UserRole::Student, 'student_id' => $student->id]);

        $this->post(route('student.login.store'), ['identifier' => '12345678'])
            ->assertRedirect(route('student.portal'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_grade_ten_student_can_login_with_temporary_code_without_pin(): void
    {
        $student = Student::factory()->create(['temporary_id' => 'TMP-2627-X1-0001', 'nis' => null, 'nisn' => null]);

        $this->post(route('student.login.store'), ['identifier' => 'TMP-2627-X1-0001'])
            ->assertRedirect(route('student.portal'));

        $user = User::where('student_id', $student->id)->firstOrFail();
        $this->assertAuthenticatedAs($user);
    }

    public function test_unknown_student_identifier_is_rejected_in_indonesian(): void
    {
        $this->post(route('student.login.store'), ['identifier' => '0000000000'])
            ->assertSessionHasErrors(['identifier' => 'NISN, NIS, atau kode sementara tidak ditemukan atau belum aktif.']);

        $this->assertGuest();
    }

    public function test_inactive_student_cannot_login_by_identifier(): void
    {
        $student = Student::factory()->create(['nisn' => '0012345679', 'status' => 'graduated']);
        User::factory()->create(['role' => UserRole::Student, 'student_id' => $student->id]);

        $this->post(route('student.login.store'), ['identifier' => '0012345679'])
            ->assertSessionHasErrors('identifier');

        $this->assertGuest();
    }

    public function test_student_login_form_does_not_request_a_pin(): void
    {
        $this->get(route('student.login'))
            ->assertOk()
            ->assertSee('NISN / NIS / Kode sementara kelas X')
            ->assertDontSee('name="password"', false)
            ->assertDontSee('PIN pribadi');
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
