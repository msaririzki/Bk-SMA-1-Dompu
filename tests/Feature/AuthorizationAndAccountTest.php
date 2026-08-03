<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AcademicYear;
use App\Models\Enrollment;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Models\ViolationCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationAndAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_counselor_can_view_all_students_but_only_update_assigned_class(): void
    {
        $year = AcademicYear::create(['name' => '2026/2027', 'is_active' => true]);
        $assignedClass = SchoolClass::create(['academic_year_id' => $year->id, 'name' => 'X-1', 'grade_level' => 'X']);
        $otherClass = SchoolClass::create(['academic_year_id' => $year->id, 'name' => 'X-2', 'grade_level' => 'X']);
        $assignedStudent = Student::factory()->create();
        $otherStudent = Student::factory()->create();
        Enrollment::create(['student_id' => $assignedStudent->id, 'academic_year_id' => $year->id, 'class_id' => $assignedClass->id]);
        Enrollment::create(['student_id' => $otherStudent->id, 'academic_year_id' => $year->id, 'class_id' => $otherClass->id]);

        $user = User::factory()->create(['role' => UserRole::Counselor]);
        $teacher = Teacher::create(['user_id' => $user->id, 'name' => $user->name]);
        $teacher->assignedClasses()->attach($assignedClass);
        $otherCreator = User::factory()->create(['role' => UserRole::Counselor]);
        $otherCase = ViolationCase::create([
            'case_number' => 'UAT-AUTH-001',
            'student_id' => $otherStudent->id,
            'academic_year_id' => $year->id,
            'created_by' => $otherCreator->id,
            'occurred_at' => now(),
            'chronology' => 'Kasus kelas lain untuk pengujian hak akses.',
            'status' => 'open',
        ]);

        $this->actingAs($user)
            ->get(route('students.show', $otherStudent))
            ->assertOk()
            ->assertDontSee('Simpan identitas')
            ->assertDontSee(route('cases.create', ['student' => $otherStudent->id]), false);
        $this->actingAs($user)
            ->get(route('cases.create'))
            ->assertOk()
            ->assertSee($assignedStudent->name)
            ->assertDontSee($otherStudent->name);
        $this->actingAs($user)->get(route('cases.create', ['student' => $otherStudent->id]))->assertForbidden();
        $this->actingAs($user)
            ->get(route('documents.create'))
            ->assertOk()
            ->assertSee($assignedStudent->name)
            ->assertDontSee($otherStudent->name);
        $this->actingAs($user)->get(route('documents.create', ['student' => $otherStudent->id]))->assertForbidden();
        $this->actingAs($user)->get(route('home-visits.create', ['student' => $otherStudent->id]))->assertForbidden();
        $this->actingAs($user)
            ->get(route('cases.show', $otherCase))
            ->assertOk()
            ->assertDontSee('Tambah tindak lanjut')
            ->assertDontSee('Buat surat/dokumen')
            ->assertDontSee('Perbarui status');
        $this->actingAs($user)->post(route('cases.follow-up', $otherCase), [
            'type' => 'coaching',
            'status' => 'planned',
        ])->assertForbidden();
        $this->actingAs($user)->put(route('students.update', $assignedStudent), $this->studentPayload($assignedStudent, 'Nama Diperbarui'))->assertRedirect();
        $this->actingAs($user)->put(route('students.update', $otherStudent), $this->studentPayload($otherStudent, 'Tidak Boleh Berubah'))->assertForbidden();
        $this->assertSame('Nama Diperbarui', $assignedStudent->fresh()->name);
        $this->assertNotSame('Tidak Boleh Berubah', $otherStudent->fresh()->name);
    }

    public function test_coordinator_sees_bulk_student_accounts_but_not_staff_account_form(): void
    {
        $year = AcademicYear::create(['name' => '2026/2027', 'is_active' => true]);
        SchoolClass::create(['academic_year_id' => $year->id, 'name' => 'XI-1', 'grade_level' => 'XI']);
        $coordinator = User::factory()->create(['role' => UserRole::Coordinator]);

        $this->actingAs($coordinator)
            ->get(route('master.index'))
            ->assertOk()
            ->assertSee('Aktifkan portal siswa')
            ->assertDontSee(route('master.teacher'), false);

        $this->actingAs($coordinator)->post(route('master.teacher'), [])->assertForbidden();
    }

    public function test_super_admin_master_page_renders_all_account_forms(): void
    {
        AcademicYear::create(['name' => '2026/2027', 'is_active' => true]);
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);

        $this->actingAs($admin)
            ->get(route('master.index'))
            ->assertOk()
            ->assertSee(route('master.teacher'), false)
            ->assertSee(route('accounts.update', $admin), false);
    }

    public function test_bulk_account_generation_is_atomic_and_access_page_is_not_cached(): void
    {
        $year = AcademicYear::create(['name' => '2026/2027', 'is_active' => true]);
        $class = SchoolClass::create(['academic_year_id' => $year->id, 'name' => 'X-3', 'grade_level' => 'X']);
        $student = Student::factory()->create(['nisn' => '0012345678']);
        Enrollment::create(['student_id' => $student->id, 'academic_year_id' => $year->id, 'class_id' => $class->id]);
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);

        $response = $this->actingAs($admin)->post(route('students.accounts'), ['class_id' => $class->id]);

        $response->assertOk()->assertHeader('Cache-Control', 'no-store, private')->assertSee($student->name)->assertDontSee('PIN awal');
        $this->assertDatabaseHas('users', ['student_id' => $student->id, 'username' => '0012345678', 'must_change_password' => false]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'student_accounts.generated', 'subject_id' => (string) $class->id]);
    }

    public function test_bulk_account_generation_rolls_back_when_identifier_is_already_used(): void
    {
        $year = AcademicYear::create(['name' => '2026/2027', 'is_active' => true]);
        $class = SchoolClass::create(['academic_year_id' => $year->id, 'name' => 'X-4', 'grade_level' => 'X']);
        $safe = Student::factory()->create(['nisn' => '0012345679']);
        $conflicting = Student::factory()->create(['nisn' => '0012345680']);
        Enrollment::create(['student_id' => $safe->id, 'academic_year_id' => $year->id, 'class_id' => $class->id]);
        Enrollment::create(['student_id' => $conflicting->id, 'academic_year_id' => $year->id, 'class_id' => $class->id]);
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);
        User::factory()->create(['username' => '0012345680', 'role' => UserRole::Counselor]);

        $this->actingAs($admin)
            ->from(route('master.index'))
            ->post(route('students.accounts'), ['class_id' => $class->id])
            ->assertRedirect(route('master.index'))
            ->assertSessionHasErrors('class_id');

        $this->assertDatabaseMissing('users', ['student_id' => $safe->id]);
        $this->assertDatabaseMissing('users', ['student_id' => $conflicting->id]);
    }

    private function studentPayload(Student $student, string $name): array
    {
        return [
            'name' => $name,
            'nis' => $student->nis,
            'nisn' => $student->nisn,
            'gender' => $student->gender,
            'status' => $student->status->value,
        ];
    }
}
