<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Student;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function staffForm()
    {
        return view('auth.staff-login');
    }

    public function studentForm()
    {
        return view('auth.student-login');
    }

    public function staffLogin(Request $request)
    {
        $credentials = $request->validate(['username' => 'required|string', 'password' => 'required|string']);
        if (! Auth::attempt([...$credentials, 'is_active' => true], $request->boolean('remember'))) {
            throw ValidationException::withMessages(['username' => 'Username atau kata sandi tidak sesuai.']);
        }
        if (! Auth::user()->role->isStaff()) {
            Auth::logout();
            throw ValidationException::withMessages(['username' => 'Gunakan halaman masuk siswa.']);
        }
        $request->session()->regenerate();
        Auth::user()->update(['last_login_at' => now()]);

        return redirect()->intended(route('dashboard'));
    }

    public function studentLogin(Request $request, AuditService $audit)
    {
        $data = $request->validate(['identifier' => 'required|string|max:50']);
        $identifier = preg_replace('/\s+/', '', trim($data['identifier']));
        $student = Student::where('status', 'active')->where(function ($query) use ($identifier) {
            $query->where('nis', $identifier)->orWhere('nisn', $identifier)->orWhere('temporary_id', $identifier);
        })->first();
        if (! $student) {
            throw ValidationException::withMessages(['identifier' => 'NISN, NIS, atau kode sementara tidak ditemukan atau belum aktif.']);
        }

        $created = false;
        $user = DB::transaction(function () use ($student, &$created) {
            $lockedStudent = Student::whereKey($student->id)->lockForUpdate()->firstOrFail();
            $account = $lockedStudent->account()->first();
            if ($account) {
                return $account;
            }

            $username = $lockedStudent->nisn ?: ($lockedStudent->nis ?: $lockedStudent->temporary_id);
            if (User::where('username', $username)->exists()) {
                return null;
            }

            $created = true;

            return User::create([
                'name' => $lockedStudent->name,
                'username' => $username,
                'role' => UserRole::Student,
                'student_id' => $lockedStudent->id,
                'password' => Str::random(64),
                'must_change_password' => false,
                'is_active' => true,
            ]);
        });
        if (! $user?->is_active || $user->role !== UserRole::Student) {
            throw ValidationException::withMessages(['identifier' => 'NISN, NIS, atau kode sementara tidak ditemukan atau belum aktif.']);
        }

        Auth::login($user);
        $request->session()->regenerate();
        $user->update(['last_login_at' => now()]);
        if ($created) {
            $audit->record('student_account.auto_created', $user, null, [
                'student_id' => $student->id,
                'username' => $user->username,
            ]);
        }

        return redirect()->route('student.portal');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function passwordForm()
    {
        if (request()->user()->role === UserRole::Student) {
            return redirect()->route('student.portal');
        }

        return view('auth.change-password');
    }

    public function changePassword(Request $request)
    {
        if ($request->user()->role === UserRole::Student) {
            return redirect()->route('student.portal');
        }

        $data = $request->validate(['password' => 'required|string|min:8|confirmed']);
        $request->user()->update(['password' => $data['password'], 'must_change_password' => false]);

        return redirect()->route('dashboard')->with('success', 'Kata sandi berhasil diganti.');
    }
}
