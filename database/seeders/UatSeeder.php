<?php

namespace Database\Seeders;

use App\Enums\CaseStatus;
use App\Enums\DocumentType;
use App\Enums\FollowUpType;
use App\Enums\StudentStatus;
use App\Enums\UserRole;
use App\Models\AcademicYear;
use App\Models\Enrollment;
use App\Models\FollowUp;
use App\Models\HomeVisit;
use App\Models\SchoolClass;
use App\Models\SchoolDocument;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Models\ViolationCase;
use App\Models\ViolationInstrument;
use App\Services\StudentIdentityService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use LogicException;

class UatSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            throw new LogicException('UatSeeder tidak boleh dijalankan pada lingkungan production.');
        }

        $password = (string) env('UAT_PASSWORD');
        if ($password === '') {
            throw new LogicException('Isi UAT_PASSWORD sebelum menjalankan UatSeeder.');
        }

        DB::transaction(function () use ($password): void {
            AcademicYear::query()->update(['is_active' => false]);
            $year = AcademicYear::updateOrCreate(['name' => '2026/2027'], [
                'starts_at' => '2026-07-01',
                'ends_at' => '2027-06-30',
                'is_active' => true,
            ]);

            $coordinator = $this->staffAccount('uat.koordinator', 'Koordinator BK UAT', UserRole::Coordinator, $password);
            $counselorOne = $this->staffAccount('uat.guru1', 'Guru BK UAT Satu', UserRole::Counselor, $password);
            $counselorTwo = $this->staffAccount('uat.guru2', 'Guru BK UAT Dua', UserRole::Counselor, $password);

            $coordinatorTeacher = $this->teacher($coordinator, '99990001');
            $teacherOne = $this->teacher($counselorOne, '99990002');
            $teacherTwo = $this->teacher($counselorTwo, '99990003');

            $classOne = SchoolClass::updateOrCreate(
                ['academic_year_id' => $year->id, 'name' => 'X-UAT-1'],
                ['grade_level' => 'X', 'track' => 'UAT', 'group_number' => 1, 'homeroom_teacher_id' => $coordinatorTeacher->id],
            );
            $classTwo = SchoolClass::updateOrCreate(
                ['academic_year_id' => $year->id, 'name' => 'X-UAT-2'],
                ['grade_level' => 'X', 'track' => 'UAT', 'group_number' => 2, 'homeroom_teacher_id' => $coordinatorTeacher->id],
            );
            $teacherOne->assignedClasses()->sync([$classOne->id]);
            $teacherTwo->assignedClasses()->sync([$classTwo->id]);

            $studentOne = $this->student('TMP-UAT-001', '99001', '0099000001', 'Siswa Uji Awan', 'L');
            $studentTwo = $this->student('TMP-UAT-002', '99002', '0099000002', 'Siswa Uji Bunga', 'P');
            $studentThree = $this->student('TMP-UAT-003', null, null, 'Siswa Uji Cakra', 'L');
            $this->enroll($studentOne, $year, $classOne, 1);
            $this->enroll($studentTwo, $year, $classTwo, 1);
            $this->enroll($studentThree, $year, $classOne, 2);
            $this->studentAccount($studentOne, '0099000001', $password);
            $this->studentAccount($studentTwo, '0099000002', $password);
            $this->studentAccount($studentThree, 'TMP-UAT-003', $password);

            $lightInstrument = ViolationInstrument::query()->where('is_active', true)->orderBy('points')->firstOrFail();
            $heavyInstrument = ViolationInstrument::query()->where('is_active', true)->where('points', '>=', 50)->orderBy('points')->firstOrFail();

            $caseOne = $this->violationCase(
                'UAT-2026-0001',
                $studentOne,
                $year,
                $counselorOne,
                CaseStatus::InFollowUp,
                now()->subDays(3),
                'Ruang kelas UAT',
                'Kronologi anonim untuk memeriksa alur pencatatan, tindak lanjut, dan dokumen.',
            );
            $this->snapshotInstrument($caseOne, $lightInstrument);
            FollowUp::updateOrCreate(
                ['case_id' => $caseOne->id, 'type' => FollowUpType::ParentSummons->value],
                [
                    'created_by' => $counselorOne->id,
                    'scheduled_at' => today()->addDay(),
                    'completed_at' => null,
                    'parent_name' => 'Wali Siswa Uji',
                    'parent_contact' => '080000000001',
                    'notes' => 'Agenda UAT untuk menguji prioritas dan arsip pemanggilan.',
                    'status' => 'planned',
                ],
            );

            $caseTwo = $this->violationCase(
                'UAT-2026-0002',
                $studentTwo,
                $year,
                $counselorTwo,
                CaseStatus::Open,
                now()->subDay(),
                'Lingkungan sekolah UAT',
                'Kasus anonim berpoin tinggi untuk memeriksa kategori prioritas dan pembatasan kelas binaan.',
            );
            $this->snapshotInstrument($caseTwo, $heavyInstrument);

            SchoolDocument::updateOrCreate(['number' => 'UAT/DOC/001'], [
                'student_id' => $studentOne->id,
                'case_id' => $caseOne->id,
                'academic_year_id' => $year->id,
                'created_by' => $counselorOne->id,
                'type' => DocumentType::Statement,
                'document_date' => today(),
                'status' => 'final',
                'payload' => [
                    'subject' => 'Surat Pernyataan UAT',
                    'body' => 'Isi anonim untuk memeriksa tata letak PDF dan ruang tanda tangan manual.',
                    'parent_name' => 'Wali Siswa Uji',
                    'parent_contact' => '080000000001',
                ],
            ]);

            $homeVisitDocument = SchoolDocument::updateOrCreate(['number' => 'UAT/HV/001'], [
                'student_id' => $studentOne->id,
                'case_id' => $caseOne->id,
                'academic_year_id' => $year->id,
                'created_by' => $counselorOne->id,
                'type' => DocumentType::HomeVisit,
                'document_date' => today(),
                'status' => 'final',
                'payload' => [],
            ]);
            HomeVisit::updateOrCreate(['document_id' => $homeVisitDocument->id], [
                'counselee_name' => $studentOne->name,
                'class_name' => $classOne->name,
                'gender' => $studentOne->gender,
                'address' => 'Alamat anonim UAT, Dompu',
                'parent_name' => 'Wali Siswa Uji',
                'problem' => 'Permasalahan anonim untuk pemeriksaan formulir laporan kunjungan rumah.',
                'purpose' => 'Mengonfirmasi kondisi dan menyepakati rencana pembinaan.',
                'visit_date' => today(),
                'met_with' => 'Orang tua/wali',
                'result' => 'Pihak yang ditemui memahami informasi dan bersedia bekerja sama.',
                'follow_up' => 'Pemantauan bersama selama dua minggu dan evaluasi terjadwal.',
                'counselor_name' => $teacherOne->name,
                'counselor_nip' => $teacherOne->nip,
                'homeroom_name' => 'Wali Kelas UAT',
                'homeroom_nip' => '99990004',
                'coordinator_name' => $coordinatorTeacher->name,
                'coordinator_nip' => $coordinatorTeacher->nip,
                'place' => 'Dompu',
            ]);
        });
    }

    private function staffAccount(string $username, string $name, UserRole $role, string $password): User
    {
        return User::updateOrCreate(['username' => $username], [
            'name' => $name,
            'email' => null,
            'password' => Hash::make($password),
            'role' => $role,
            'student_id' => null,
            'must_change_password' => false,
            'is_active' => true,
        ]);
    }

    private function teacher(User $user, string $nip): Teacher
    {
        return Teacher::updateOrCreate(['user_id' => $user->id], [
            'nip' => $nip,
            'name' => $user->name,
            'phone' => null,
            'is_counselor' => true,
        ]);
    }

    private function student(string $temporaryId, ?string $nis, ?string $nisn, string $name, string $gender): Student
    {
        $student = Student::withTrashed()->updateOrCreate(['temporary_id' => $temporaryId], [
            'nis' => $nis,
            'nisn' => $nisn,
            'name' => $name,
            'normalized_name' => app(StudentIdentityService::class)->normalizeName($name),
            'gender' => $gender,
            'status' => StudentStatus::Active,
        ]);
        if ($student->trashed()) {
            $student->restore();
        }

        return $student;
    }

    private function enroll(Student $student, AcademicYear $year, SchoolClass $class, int $rollNumber): void
    {
        Enrollment::updateOrCreate(
            ['student_id' => $student->id, 'academic_year_id' => $year->id],
            ['class_id' => $class->id, 'roll_number' => $rollNumber, 'status' => 'active'],
        );
    }

    private function studentAccount(Student $student, string $username, string $password): void
    {
        User::updateOrCreate(['student_id' => $student->id], [
            'name' => $student->name,
            'username' => $username,
            'email' => null,
            'password' => Hash::make($password),
            'role' => UserRole::Student,
            'must_change_password' => false,
            'is_active' => true,
        ]);
    }

    private function violationCase(
        string $number,
        Student $student,
        AcademicYear $year,
        User $creator,
        CaseStatus $status,
        mixed $occurredAt,
        string $location,
        string $chronology,
    ): ViolationCase {
        $case = ViolationCase::withTrashed()->updateOrCreate(['case_number' => $number], [
            'student_id' => $student->id,
            'academic_year_id' => $year->id,
            'created_by' => $creator->id,
            'occurred_at' => $occurredAt,
            'location' => $location,
            'chronology' => $chronology,
            'status' => $status,
            'cancellation_reason' => null,
            'resolved_at' => $status === CaseStatus::Resolved ? now() : null,
        ]);
        if ($case->trashed()) {
            $case->restore();
        }

        return $case;
    }

    private function snapshotInstrument(ViolationCase $case, ViolationInstrument $instrument): void
    {
        $case->items()->delete();
        $case->items()->create([
            'instrument_id' => $instrument->id,
            'instrument_code' => $instrument->code,
            'instrument_name' => $instrument->name,
            'points' => $instrument->points,
            'sanction_snapshot' => $instrument->sanction,
        ]);
    }
}
