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
        <p class="mt-2 text-sm leading-6 text-slate-500">Masukkan NISN, NIS, kode sementara kelas X, atau nama lengkap sesuai data sekolah.</p>
    </div>
    @if($errors->any())
        <div class="alert alert-danger mt-5"><x-icon name="alert" class="size-5 shrink-0" /><span>{{ $errors->first() }}</span></div>
    @endif
    <form method="post" class="mt-7 space-y-5">
        @csrf
        <div>
            <label class="form-label" for="identifier">NISN / NIS / Kode sementara / Nama lengkap</label>
            <div class="input-with-icon"><x-icon name="user" /><input id="identifier" class="form-control" name="identifier" value="{{ old('identifier') }}" autocomplete="username" required autofocus placeholder="Masukkan identitas atau nama lengkap siswa"></div>
            <p class="field-help">Jika memakai nama, masukkan nama lengkap dengan ejaan yang benar. Nama sebagian atau salah eja tidak dapat digunakan.</p>
        </div>
        <button class="btn btn-student w-full justify-center">Cek pelanggaran siswa <x-icon name="arrow-right" class="size-4" /></button>
    </form>
    <div class="mt-6 rounded-2xl bg-slate-50 p-4 text-center text-xs leading-5 text-slate-500">Belum mengetahui identitas atau nama lengkap sesuai data sekolah? Hubungi Guru BK atau wali kelas.</div>
</div>
@endsection
