<?php

namespace Tests\Feature;

use App\Enums\CaseStatus;
use App\Enums\UserRole;
use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\User;
use App\Models\ViolationCase;
use App\Services\AttachmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AttachmentSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_image_is_reencoded_and_thumbnail_stays_private(): void
    {
        Storage::fake('local');
        $year = AcademicYear::create(['name' => '2026/2027', 'is_active' => true]);
        $student = Student::factory()->create();
        $user = User::factory()->create(['role' => UserRole::Coordinator]);
        $case = ViolationCase::create(['case_number' => 'BK-2026-00001', 'student_id' => $student->id, 'academic_year_id' => $year->id, 'created_by' => $user->id, 'occurred_at' => now(), 'chronology' => 'Dokumentasi pengujian keamanan lampiran.', 'status' => CaseStatus::Open]);
        $attachment = app(AttachmentService::class)->store(UploadedFile::fake()->image('bukti.jpg', 800, 600), $case, $user);
        Storage::disk('local')->assertExists($attachment->path);
        Storage::disk('local')->assertExists($attachment->thumbnail_path);
        $this->assertStringStartsWith('evidence/'.$case->id, $attachment->path);
        $this->actingAs(User::factory()->create(['role' => UserRole::Student, 'student_id' => $student->id]))->get(route('attachments.download', $attachment))->assertForbidden();
    }
}
