@extends('layouts.auth')
@section('title', 'Masuk Staf')
@section('content')
<div class="auth-card">
    <div class="flex items-start justify-between gap-4">
        <div class="icon-tile"><x-icon name="shield" class="size-6" /></div>
        <span class="badge badge-emerald">Portal staf</span>
    </div>
    <div class="mt-6">
        <p class="page-eyebrow">Akses internal</p>
        <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-navy-950">Selamat datang kembali</h1>
        <p class="mt-2 text-sm leading-6 text-slate-500">Masuk menggunakan akun admin, koordinator, atau guru BK.</p>
    </div>
    @if($errors->any())
        <div class="alert alert-danger mt-5"><x-icon name="alert" class="size-5 shrink-0" /><span>{{ $errors->first() }}</span></div>
    @endif
    <form method="post" class="mt-7 space-y-5">
        @csrf
        <div>
            <label class="form-label" for="username">Username</label>
            <div class="input-with-icon"><x-icon name="user" /><input id="username" class="form-control" name="username" value="{{ old('username') }}" autocomplete="username" required autofocus></div>
        </div>
        <div>
            <label class="form-label" for="password">Kata sandi</label>
            <div class="input-with-icon"><x-icon name="lock" /><input id="password" class="form-control" type="password" name="password" autocomplete="current-password" required></div>
        </div>
        <label class="flex cursor-pointer items-center gap-2.5 text-sm font-medium text-slate-600"><input class="size-4 rounded border-slate-300 accent-teal-600" type="checkbox" name="remember" value="1"> Ingat saya pada perangkat ini</label>
        <button class="btn btn-primary w-full justify-center">Masuk ke aplikasi <x-icon name="arrow-right" class="size-4" /></button>
    </form>
    <div class="mt-6 border-t border-slate-100 pt-5 text-center">
        <a href="{{ route('student.login') }}" class="inline-flex items-center gap-2 text-sm font-bold text-teal-700 transition hover:text-teal-800">Saya seorang siswa <x-icon name="arrow-right" class="size-4" /></a>
    </div>
</div>
@endsection
