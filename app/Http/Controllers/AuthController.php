<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

    public function studentLogin(Request $request)
    {
        $data = $request->validate(['identifier' => 'required|string|max:50']);
        $identifier = preg_replace('/\s+/', '', trim($data['identifier']));
        $user = User::where('role', UserRole::Student)->where('is_active', true)
            ->whereHas('student', fn ($student) => $student->where('status', 'active'))
            ->where(function ($q) use ($identifier) {
                $q->where('username', $identifier)->orWhereHas('student', fn ($s) => $s->where('nis', $identifier)->orWhere('nisn', $identifier)->orWhere('temporary_id', $identifier));
            })->first();
        if (! $user) {
            throw ValidationException::withMessages(['identifier' => 'NISN, NIS, atau kode sementara tidak ditemukan atau belum aktif.']);
        }
        Auth::login($user);
        $request->session()->regenerate();
        $user->update(['last_login_at' => now()]);

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
