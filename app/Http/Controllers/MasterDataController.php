<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Teacher;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterDataController extends Controller
{
    public function index()
    {
        return view('app.master.index', ['years' => AcademicYear::orderByDesc('name')->get(), 'classes' => SchoolClass::with(['academicYear', 'homeroomTeacher'])->orderBy('name')->get(), 'teachers' => Teacher::with(['user', 'assignedClasses'])->orderBy('name')->get(), 'users' => User::where('role', '!=', 'student')->orderBy('name')->get()]);
    }

    public function year(Request $r, AuditService $audit)
    {
        $d = $r->validate(['name' => 'required|string|max:20|unique:academic_years,name', 'is_active' => 'nullable|boolean']);
        if ($r->boolean('is_active')) {
            AcademicYear::query()->update(['is_active' => false]);
        }$year = AcademicYear::create($d + ['is_active' => $r->boolean('is_active')]);
        $audit->record('academic_year.created', $year, null, $year->toArray());

        return back()->with('success', 'Tahun pelajaran ditambahkan.');
    }

    public function classStore(Request $r, AuditService $audit)
    {
        $d = $r->validate(['academic_year_id' => 'required|exists:academic_years,id', 'name' => 'required|string|max:100', 'grade_level' => 'required|in:X,XI,XII', 'track' => 'nullable|string|max:32', 'group_number' => 'nullable|integer|min:1', 'homeroom_teacher_id' => 'nullable|exists:teachers,id']);
        $class = SchoolClass::create($d);
        $audit->record('class.created', $class, null, $class->toArray());

        return back()->with('success', 'Kelas ditambahkan.');
    }

    public function teacher(Request $r, AuditService $audit)
    {
        abort_unless($r->user()->role === UserRole::SuperAdmin, 403);
        $d = $r->validate(['name' => 'required|string|max:255', 'nip' => 'nullable|string|max:50|unique:teachers,nip', 'phone' => 'nullable|string|max:50', 'username' => 'required|string|max:100|unique:users,username', 'role' => 'required|in:coordinator_bk,guru_bk', 'password' => 'required|string|min:8']);
        $user = DB::transaction(function () use ($d) {
            $user = User::create(['name' => $d['name'], 'username' => $d['username'], 'role' => $d['role'], 'password' => $d['password'], 'must_change_password' => true, 'is_active' => true]);
            Teacher::create(['user_id' => $user->id, 'name' => $d['name'], 'nip' => $d['nip'] ?? null, 'phone' => $d['phone'] ?? null, 'is_counselor' => true]);

            return $user;
        });
        $audit->record('account.created', $user, null, $user->toArray());

        return back()->with('success', 'Akun guru BK dibuat.');
    }

    public function assign(Request $r, AuditService $audit)
    {
        $d = $r->validate(['teacher_id' => 'required|exists:teachers,id', 'class_ids' => 'nullable|array', 'class_ids.*' => 'exists:classes,id']);
        $teacher = Teacher::findOrFail($d['teacher_id']);
        $before = $teacher->assignedClasses()->pluck('classes.id')->all();
        $teacher->assignedClasses()->sync($d['class_ids'] ?? []);
        $audit->record('counselor_assignment.updated', $teacher, ['class_ids' => $before], ['class_ids' => $d['class_ids'] ?? []]);

        return back()->with('success', 'Kelas binaan diperbarui.');
    }

    public function account(Request $r, User $user, AuditService $audit)
    {
        abort_unless($r->user()->role === UserRole::SuperAdmin, 403);
        $d = $r->validate(['role' => 'required|in:super_admin,coordinator_bk,guru_bk', 'is_active' => 'nullable|boolean', 'password' => 'nullable|string|min:8']);
        $active = $r->boolean('is_active');
        if ($user->is($r->user()) && (! $active || $d['role'] !== UserRole::SuperAdmin->value)) {
            return back()->withErrors(['account' => 'Akun super admin yang sedang digunakan tidak dapat dinonaktifkan atau diturunkan perannya.']);
        }if ($user->role === UserRole::SuperAdmin && $d['role'] !== UserRole::SuperAdmin->value && User::where('role', UserRole::SuperAdmin)->where('is_active', true)->count() <= 1) {
            return back()->withErrors(['account' => 'Minimal satu super admin aktif harus tersedia.']);
        }$before = $user->toArray();
        $payload = ['role' => $d['role'], 'is_active' => $active];
        if (! empty($d['password'])) {
            $payload += ['password' => $d['password'], 'must_change_password' => true];
        }$user->update($payload);
        $audit->record('account.updated', $user, $before, $user->fresh()->toArray());

        return back()->with('success', 'Akun diperbarui.');
    }
}
