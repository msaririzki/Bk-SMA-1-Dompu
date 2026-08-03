<!doctype html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>@yield('title','Dashboard') — Sistem BK</title>@vite(['resources/css/app.css','resources/js/app.js'])</head>
<body>
<aside data-sidebar class="fixed inset-y-0 left-0 z-50 w-72 -translate-x-full bg-navy-950 p-4 text-white transition-transform lg:translate-x-0">
    <button data-nav-toggle class="absolute right-4 top-4 grid size-9 place-items-center rounded-lg border border-white/15 text-xl text-white lg:hidden" type="button" aria-label="Tutup menu">×</button>
    <a href="{{ route('dashboard') }}" class="mb-7 flex items-center gap-3 px-2"><span class="grid size-11 place-items-center rounded-xl bg-teal-500 font-black">BK</span><span><strong class="block">SMAN 1 Dompu</strong><small class="text-slate-400">Administrasi BK</small></span></a>
    <nav class="space-y-1">
        <a class="nav-link {{ request()->routeIs('dashboard')?'active':'' }}" href="{{ route('dashboard') }}">Dashboard</a>
        <a class="nav-link {{ request()->routeIs('students.*')?'active':'' }}" href="{{ route('students.index') }}">Data siswa</a>
        <a class="nav-link {{ request()->routeIs('cases.*')?'active':'' }}" href="{{ route('cases.index') }}">Pelanggaran</a>
        <a class="nav-link {{ request()->routeIs('documents.*','home-visits.*')?'active':'' }}" href="{{ route('documents.index') }}">Dokumen & home visit</a>
        @if(auth()->user()->hasRole(\App\Enums\UserRole::SuperAdmin,\App\Enums\UserRole::Coordinator))
            <a class="nav-link {{ request()->routeIs('instruments.*')?'active':'' }}" href="{{ route('instruments.index') }}">Instrumen & skor</a>
            <a class="nav-link {{ request()->routeIs('imports.*')?'active':'' }}" href="{{ route('imports.index') }}">Impor siswa</a>
            <a class="nav-link {{ request()->routeIs('master.*')?'active':'' }}" href="{{ route('master.index') }}">Master data</a>
        @endif
        @if(auth()->user()->role===\App\Enums\UserRole::SuperAdmin)<a class="nav-link {{ request()->routeIs('cms.*')?'active':'' }}" href="{{ route('cms.index') }}">Konten website</a><a class="nav-link {{ request()->routeIs('audit.*')?'active':'' }}" href="{{ route('audit.index') }}">Audit aktivitas</a>@endif
    </nav>
</aside>
<div class="min-h-screen lg:pl-72">
    <header class="sticky top-0 z-30 border-b border-slate-200 bg-white/90 backdrop-blur"><div class="flex h-18 items-center justify-between px-4 sm:px-6 lg:px-8"><button data-nav-toggle class="btn btn-secondary lg:hidden">Menu</button><div class="ml-auto flex items-center gap-4"><div class="hidden text-right sm:block"><p class="text-sm font-semibold">{{ auth()->user()->name }}</p><p class="text-xs text-slate-500">{{ auth()->user()->role->label() }}</p></div><form method="post" action="{{ route('logout') }}">@csrf<button class="btn btn-secondary">Keluar</button></form></div></div></header>
    <main class="p-4 sm:p-6 lg:p-8">
        @if(session('success'))<div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('success') }}</div>@endif
        @if(session('warning'))<div class="mb-5 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">{{ session('warning') }}</div>@endif
        @if($errors->any())<div class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800"><strong>Periksa kembali isian:</strong><ul class="mt-2 list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        @yield('content')
    </main>
</div></body></html>
