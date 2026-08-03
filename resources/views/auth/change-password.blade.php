@extends('layouts.auth')
@section('title', 'Ganti Kata Sandi')
@section('content')
<div class="auth-card">
    <div class="flex items-start justify-between gap-4">
        <div class="icon-tile icon-tile-amber"><x-icon name="lock" class="size-6" /></div>
        <span class="badge badge-amber">Keamanan akun</span>
    </div>
    <div class="mt-6">
        <p class="page-eyebrow">Langkah pertama</p>
        <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-navy-950">Buat kata sandi/PIN baru</h1>
        <p class="mt-2 text-sm leading-6 text-slate-500">Gunakan sedikitnya 8 karakter dan jangan membagikannya kepada orang lain.</p>
    </div>
    @if($errors->any())
        <div class="alert alert-danger mt-5"><x-icon name="alert" class="size-5 shrink-0" /><span>{{ $errors->first() }}</span></div>
    @endif
    <form method="post" action="{{ route('password.update') }}" class="mt-7 space-y-5">
        @csrf @method('put')
        <div><label class="form-label" for="new-password">Kata sandi/PIN baru</label><div class="input-with-icon"><x-icon name="lock" /><input id="new-password" class="form-control" type="password" name="password" autocomplete="new-password" required></div></div>
        <div><label class="form-label" for="password-confirmation">Ulangi kata sandi/PIN</label><div class="input-with-icon"><x-icon name="check" /><input id="password-confirmation" class="form-control" type="password" name="password_confirmation" autocomplete="new-password" required></div></div>
        <button class="btn btn-primary w-full justify-center">Simpan dan lanjutkan <x-icon name="arrow-right" class="size-4" /></button>
    </form>
</div>
@endsection
