<?php

namespace Tests\Feature;

use App\Enums\CaseStatus;
use App\Enums\UserRole;
use App\Models\AcademicYear;
use App\Models\CmsPage;
use App\Models\SchoolDocument;
use App\Models\SeverityLevel;
use App\Models\Student;
use App\Models\User;
use App\Models\ViolationCase;
use App\Models\ViolationCategory;
use App\Models\ViolationInstrument;
use App\Services\AttachmentService;
use App\Services\ScoringService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class SystemHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_cms_removes_all_html_attributes_and_disallowed_tags(): void
    {
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $page = CmsPage::create(['slug' => 'uji', 'title' => 'Uji', 'content' => '<p>Aman</p>', 'is_published' => true]);

        $this->actingAs($admin)->put(route('cms.update', $page), [
            'title' => 'Uji',
            'content' => '<p onclick="alert(1)" style="background:url(https://example.test)">Aman</p><script>alert(2)</script>',
            'is_published' => '1',
        ])->assertRedirect();

        $content = $page->fresh()->content;
        $this->assertStringContainsString('<p>Aman</p>', $content);
        $this->assertStringNotContainsString('onclick', $content);
        $this->assertStringNotContainsString('style=', $content);
        $this->assertStringNotContainsString('<script', $content);
    }

    public function test_overlapping_severity_ranges_are_rejected(): void
    {
        $first = SeverityLevel::create(['name' => 'Ringan', 'min_points' => 1, 'max_points' => 24]);
        $second = SeverityLevel::create(['name' => 'Sedang', 'min_points' => 25, 'max_points' => 49]);
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);

        $this->actingAs($admin)->from(route('instruments.index'))->put(route('severities.update'), [
            'levels' => [
                ['id' => $first->id, 'name' => 'Ringan', 'min_points' => 1, 'max_points' => 30],
                ['id' => $second->id, 'name' => 'Sedang', 'min_points' => 25, 'max_points' => 49],
            ],
        ])->assertRedirect(route('instruments.index'))->assertSessionHasErrors('levels');

        $this->assertSame(24, $first->fresh()->max_points);
    }

    public function test_percentage_uses_highest_open_ended_threshold(): void
    {
        $year = AcademicYear::create(['name' => '2026/2027', 'is_active' => true]);
        SeverityLevel::create(['name' => 'Ringan', 'min_points' => 1, 'max_points' => 49]);
        SeverityLevel::create(['name' => 'Berat', 'min_points' => 50, 'max_points' => 149]);
        SeverityLevel::create(['name' => 'Ambang Tindakan', 'min_points' => 150, 'max_points' => null]);
        $student = Student::factory()->create();

        $summary = app(ScoringService::class)->summary($student, $year->id);

        $this->assertSame(150, $summary['threshold']);
        $this->assertSame(0.0, $summary['percentage']);
    }

    public function test_case_number_is_not_reused_after_soft_delete(): void
    {
        $year = AcademicYear::create(['name' => '2026/2027', 'is_active' => true]);
        $student = Student::factory()->create();
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $category = ViolationCategory::create(['code' => 'A', 'name' => 'Disiplin']);
        $instrument = ViolationInstrument::create(['category_id' => $category->id, 'code' => 'A01', 'name' => 'Terlambat', 'points' => 5, 'is_active' => true]);

        $this->actingAs($admin)->post(route('cases.store'), $this->casePayload($student, $instrument))->assertRedirect();
        ViolationCase::firstOrFail()->delete();
        $this->actingAs($admin)->post(route('cases.store'), $this->casePayload($student, $instrument))->assertRedirect();

        $numbers = ViolationCase::withTrashed()->orderBy('case_number')->pluck('case_number');
        $this->assertSame(['BK-'.now()->format('Y').'-00001', 'BK-'.now()->format('Y').'-00002'], $numbers->all());
    }

    public function test_document_rejects_case_from_another_student(): void
    {
        $year = AcademicYear::create(['name' => '2026/2027', 'is_active' => true]);
        $student = Student::factory()->create();
        $other = Student::factory()->create();
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $case = ViolationCase::create(['case_number' => 'BK-2026-00001', 'student_id' => $other->id, 'academic_year_id' => $year->id, 'created_by' => $admin->id, 'occurred_at' => now(), 'chronology' => 'Kasus milik siswa lain untuk pengujian.', 'status' => CaseStatus::Open]);

        $this->actingAs($admin)->post(route('documents.store'), [
            'student_id' => $student->id,
            'case_id' => $case->id,
            'type' => 'statement',
            'document_date' => now()->toDateString(),
            'body' => 'Isi surat pernyataan untuk pengujian keterkaitan kasus.',
        ])->assertStatus(422);

        $this->assertSame(0, SchoolDocument::count());
    }

    public function test_attachment_requires_valid_signature_and_staff_authorization(): void
    {
        Storage::fake('local');
        $year = AcademicYear::create(['name' => '2026/2027', 'is_active' => true]);
        $student = Student::factory()->create();
        $staff = User::factory()->create(['role' => UserRole::Coordinator]);
        $case = ViolationCase::create(['case_number' => 'BK-2026-00001', 'student_id' => $student->id, 'academic_year_id' => $year->id, 'created_by' => $staff->id, 'occurred_at' => now(), 'chronology' => 'Pengujian akses berkas privat.', 'status' => CaseStatus::Open]);
        $attachment = app(AttachmentService::class)->store(UploadedFile::fake()->create('bukti.pdf', 50, 'application/pdf'), $case, $staff);

        $this->actingAs($staff)->get(route('attachments.download', $attachment))->assertForbidden();
        $signed = URL::temporarySignedRoute('attachments.download', now()->addMinutes(10), ['attachment' => $attachment]);
        $this->actingAs($staff)->get($signed)->assertOk();
    }

    public function test_recap_without_active_academic_year_returns_validation_status(): void
    {
        $student = Student::factory()->create();
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);

        $this->actingAs($admin)->get(route('students.recap', $student))->assertStatus(422);
    }

    private function casePayload(Student $student, ViolationInstrument $instrument): array
    {
        return [
            'student_id' => $student->id,
            'occurred_at' => now()->format('Y-m-d H:i:s'),
            'location' => 'Gerbang sekolah',
            'chronology' => 'Siswa datang setelah bel masuk sekolah berbunyi.',
            'instrument_ids' => [$instrument->id],
        ];
    }
}
