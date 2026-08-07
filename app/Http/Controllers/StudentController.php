<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentAlias;
use App\Models\User;
use App\Services\AuditService;
use App\Services\ScoringService;
use App\Services\StudentIdentityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $term = trim((string) $request->q);
        $classId = $request->integer('class_id');
        $students = Student::with(['currentEnrollment.schoolClass'])->when($term, fn ($q) => $q->where(function ($x) use ($term) {
            $x->where('name', 'like', "%{$term}%")->orWhere('nis', $term)->orWhere('nisn', $term)->orWhere('temporary_id', $term)->orWhereHas('aliases', fn ($alias) => $alias->where('name', 'like', "%{$term}%"));
        }))->when($classId, fn ($q) => $q->whereHas('currentEnrollment', fn ($e) => $e->where('class_id', $classId)))->when($request->status, fn ($q, $status) => $q->where('status', $status))->orderBy('name')->paginate(20)->withQueryString();

        return view('app.students.index', ['students' => $students, 'classes' => SchoolClass::with('academicYear')->orderBy('name')->get()]);
    }

    public function search(Request $request, StudentIdentityService $identity)
    {
        $data = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:100'],
        ]);
        $term = trim($data['q']);
        $normalizedTerm = $identity->normalizeName($term);
        $user = $request->user();

        $students = Student::query()
            ->with('currentEnrollment.schoolClass')
            ->where('status', 'active')
            ->when($user->role === UserRole::Counselor, function ($query) use ($user): void {
                $classIds = $user->teacher?->assignedClasses()->pluck('classes.id') ?? collect();
                $query->whereHas('currentEnrollment', fn ($enrollment) => $enrollment->whereIn('class_id', $classIds));
            })
            ->where(function ($query) use ($term, $normalizedTerm): void {
                $query->where('name', 'like', "%{$term}%")
                    ->orWhere('normalized_name', 'like', "%{$normalizedTerm}%")
                    ->orWhere('nis', 'like', "%{$term}%")
                    ->orWhere('nisn', 'like', "%{$term}%")
                    ->orWhere('temporary_id', 'like', "%{$term}%")
                    ->orWhereHas('aliases', fn ($alias) => $alias->where('name', 'like', "%{$term}%"));
            })
            ->orderBy('name')
            ->limit(20)
            ->get()
            ->map(function (Student $student): array {
                $identifier = $student->nisn
                    ? ['label' => 'NISN', 'value' => $student->nisn]
                    : ($student->nis
                        ? ['label' => 'NIS', 'value' => $student->nis]
                        : ['label' => 'ID sementara', 'value' => $student->temporary_id]);

                return [
                    'id' => $student->id,
                    'name' => $student->name,
                    'class_name' => $student->currentEnrollment?->schoolClass?->name ?? 'Tanpa kelas',
                    'identifier_label' => $identifier['label'],
                    'identifier' => $identifier['value'],
                ];
            });

        return response()
            ->json(['results' => $students])
            ->header('Cache-Control', 'no-store, private');
    }

    public function show(Student $student, ScoringService $scoring)
    {
        $this->authorize('view', $student);
        $year = AcademicYear::active();
        $student->load(['enrollments.schoolClass', 'cases' => fn ($q) => $q->with('items')->latest('occurred_at')]);

        return view('app.students.show', ['student' => $student, 'year' => $year, 'score' => $year ? $scoring->summary($student, $year->id) : null]);
    }

    public function update(Request $request, Student $student, StudentIdentityService $identity, AuditService $audit)
    {
        $this->authorize('update', $student);
        $data = $request->validate(['name' => 'required|string|max:255', 'nis' => 'nullable|digits_between:4,20|unique:students,nis,'.$student->id.',id', 'nisn' => 'nullable|digits:10|unique:students,nisn,'.$student->id.',id', 'gender' => 'required|in:L,P', 'status' => 'required|in:active,graduated,transferred,withdrawn']);
        $before = $student->toArray();
        $data['normalized_name'] = $identity->normalizeName($data['name']);
        DB::transaction(function () use ($audit, $before, $data, $student): void {
            if ($student->name !== $data['name']) {
                StudentAlias::firstOrCreate(
                    ['student_id' => $student->id, 'normalized_name' => $student->normalized_name],
                    ['name' => $student->name, 'source' => 'manual_update'],
                );
            }
            $student->update($data);
            $audit->record('student.updated', $student, $before, $student->fresh()->toArray());
        });

        return back()->with('success', 'Data siswa diperbarui.');
    }

    public function generateAccounts(Request $request, AuditService $audit)
    {
        abort_unless($request->user()->hasRole(UserRole::SuperAdmin, UserRole::Coordinator), 403);
        $data = $request->validate(['class_id' => 'required|exists:classes,id']);
        $class = SchoolClass::findOrFail($data['class_id']);
        $result = DB::transaction(function () use ($class) {
            SchoolClass::whereKey($class->id)->lockForUpdate()->firstOrFail();
            $credentials = [];
            $created = 0;
            foreach ($class->enrollments()->with('student.account')->get() as $enrollment) {
                $student = $enrollment->student;
                $username = $student->nisn ?: ($student->nis ?: $student->temporary_id);
                if (! $student->account) {
                    if (User::where('username', $username)->exists()) {
                        throw ValidationException::withMessages(['class_id' => "Identitas masuk {$username} sudah digunakan akun lain. Periksa data siswa sebelum membuat akun."]);
                    }
                    User::create(['name' => $student->name, 'username' => $username, 'role' => UserRole::Student, 'student_id' => $student->id, 'password' => Str::random(64), 'must_change_password' => false, 'is_active' => true]);
                    $created++;
                }
                $credentials[] = ['name' => $student->name, 'username' => $username];
            }

            return compact('credentials', 'created');
        });
        $audit->record('student_accounts.generated', $class, null, ['class_id' => $class->id, 'created' => $result['created'], 'listed' => count($result['credentials'])]);
        $credentials = $result['credentials'];

        return response()
            ->view('app.students.credentials', compact('credentials', 'class'))
            ->header('Cache-Control', 'no-store, private');
    }
}
