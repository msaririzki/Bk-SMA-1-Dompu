<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\User;
use App\Models\ViolationCategory;
use App\Models\ViolationInstrument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ViolationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_case_snapshots_instrument_points_and_name(): void
    {
        $year = AcademicYear::create(['name' => '2026/2027', 'is_active' => true]);
        $student = Student::factory()->create();
        $user = User::factory()->create(['role' => UserRole::Coordinator]);
        $cat = ViolationCategory::create(['code' => 'A', 'name' => 'Keterlambatan']);
        $instrument = ViolationInstrument::create(['category_id' => $cat->id, 'code' => 'A01', 'name' => 'Terlambat', 'points' => 5, 'sanction' => 'Pembinaan', 'is_active' => true]);
        $response = $this->actingAs($user)->post(route('cases.store'), ['student_id' => $student->id, 'occurred_at' => '2026-08-03 07:10:00', 'location' => 'Gerbang', 'chronology' => 'Siswa datang setelah bel masuk sekolah berbunyi.', 'instrument_ids' => [$instrument->id]]);
        $case = $student->cases()->first();
        $response->assertRedirect(route('cases.show', $case));
        $this->assertSame(5, $case->items()->first()->points);
        $instrument->update(['points' => 10, 'name' => 'Terlambat diperbarui']);
        $this->assertSame(5, $case->items()->first()->points);
        $this->assertSame('Terlambat', $case->items()->first()->instrument_name);
    }
}
