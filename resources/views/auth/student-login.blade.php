@extends('layouts.auth')
@section('title', 'Masuk Siswa')
@section('content')
<div class="auth-card">
    <div class="flex items-start justify-between gap-4">
        <div class="icon-tile icon-tile-amber"><x-icon name="students" class="size-6" /></div>
        <span class="badge badge-amber">Portal siswa</span>
    </div>
    <div class="mt-6">
        <p class="page-eyebrow">Data pribadi</p>
        <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-navy-950">Lihat data saya</h1>
        <p class="mt-2 text-sm leading-6 text-slate-500">Gunakan NISN, NIS, atau ID sementara dan PIN pribadi.</p>
    </div>
    @if($errors->any())
        <div class="alert alert-danger mt-5"><x-icon name="alert" class="size-5 shrink-0" /><span>{{ $errors->first() }}</span></div>
    @endif
    <form method="post" class="mt-7 space-y-5">
        @csrf
        <div>
            <label class="form-label" for="identifier">NISN / NIS / ID sementara</label>
            <div class="input-with-icon"><x-icon name="user" /><input id="identifier" class="form-control" name="identifier" value="{{ old('identifier') }}" autocomplete="username" required autofocus></div>
        </div>
        <div>
            <label class="form-label" for="student-password">PIN</label>
            <div class="input-with-icon"><x-icon name="lock" /><input id="student-password" class="form-control" type="password" name="password" inputmode="numeric" autocomplete="current-password" required></div>
        </div>
        <button class="btn btn-accent w-full justify-center">Masuk sebagai siswa <x-icon name="arrow-right" class="size-4" /></button>
    </form>
    <div class="mt-6 rounded-2xl bg-slate-50 p-4 text-center text-xs leading-5 text-slate-500">Data hanya dapat dilihat oleh pemilik akun. Hubungi guru BK jika lupa PIN.</div>
</div>
@endsection
