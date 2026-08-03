<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use App\Services\AuditService;
use App\Services\ScoringService;
use App\Services\StudentIdentityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
        $student->update($data);
        $audit->record('student.updated', $student, $before, $student->fresh()->toArray());

        return back()->with('success', 'Data siswa diperbarui.');
    }

    public function generateAccounts(Request $request)
    {
        abort_unless($request->user()->hasRole(UserRole::SuperAdmin, UserRole::Coordinator), 403);
        $data = $request->validate(['class_id' => 'required|exists:classes,id']);
        $class = SchoolClass::findOrFail($data['class_id']);
        $credentials = [];
        foreach ($class->enrollments()->with('student.account')->get() as $enrollment) {
            $student = $enrollment->student;
            if ($student->account) {
                continue;
            }$username = $student->nisn ?: ($student->nis ?: $student->temporary_id);
            $pin = (string) random_int(10000000, 99999999);
            User::create(['name' => $student->name, 'username' => $username, 'role' => UserRole::Student, 'student_id' => $student->id, 'password' => Hash::make($pin), 'must_change_password' => true, 'is_active' => true]);
            $credentials[] = ['name' => $student->name, 'username' => $username, 'pin' => $pin];
        }

        return view('app.students.credentials',compact('credentials','class'));
    }
}
