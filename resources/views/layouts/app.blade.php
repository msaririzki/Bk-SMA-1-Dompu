<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#061423">
    <title>@yield('title', 'Dashboard') — Sistem BK</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="app-shell">
    <div data-sidebar-overlay class="hidden lg:hidden"></div>
    <aside data-sidebar class="app-sidebar fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col px-4 py-5 text-white transition-transform duration-300 lg:translate-x-0">
        <div class="flex items-center justify-between px-2">
            <a href="{{ route('dashboard') }}" class="flex min-w-0 items-center gap-3">
                @if ($schoolLogo = \App\Models\SchoolSetting::value('school_logo'))
                    <img class="size-11 rounded-2xl bg-white object-contain p-1.5 shadow-lg shadow-black/20" src="{{ Storage::disk('public')->url($schoolLogo) }}" alt="Logo sekolah">
                @else
                    <span class="grid size-11 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-teal-400 to-teal-600 font-black text-navy-950 shadow-lg shadow-teal-500/20">BK</span>
                @endif
                <span class="min-w-0"><strong class="block truncate text-[15px] text-white">SMAN 1 Dompu</strong><small class="mt-0.5 block text-xs font-medium text-slate-400">Administrasi BK</small></span>
            </a>
            <button data-nav-toggle class="btn btn-icon border border-white/10 bg-white/5 text-white hover:bg-white/10 lg:hidden" type="button" aria-label="Tutup menu" aria-expanded="false"><x-icon name="close" /></button>
        </div>

        <nav class="mt-7 flex-1 overflow-y-auto pb-5">
            <p class="sidebar-label">Utama</p>
            <div class="space-y-1">
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><x-icon name="dashboard" />Dashboard</a>
                <a class="nav-link {{ request()->routeIs('students.*') ? 'active' : '' }}" href="{{ route('students.index') }}"><x-icon name="students" />Data siswa</a>
                <a class="nav-link {{ request()->routeIs('cases.*') ? 'active' : '' }}" href="{{ route('cases.index') }}"><x-icon name="clipboard" />Pelanggaran</a>
                <a class="nav-link {{ request()->routeIs('documents.*', 'home-visits.*') ? 'active' : '' }}" href="{{ route('documents.index') }}"><x-icon name="documents" />Dokumen & home visit</a>
            </div>

            @if (auth()->user()->hasRole(\App\Enums\UserRole::SuperAdmin, \App\Enums\UserRole::Coordinator))
                <p class="sidebar-label">Pengelolaan</p>
                <div class="space-y-1">
                    <a class="nav-link {{ request()->routeIs('instruments.*', 'severities.*') ? 'active' : '' }}" href="{{ route('instruments.index') }}"><x-icon name="scale" />Instrumen & skor</a>
                    <a class="nav-link {{ request()->routeIs('imports.*') ? 'active' : '' }}" href="{{ route('imports.index') }}"><x-icon name="upload" />Impor siswa</a>
                    <a class="nav-link {{ request()->routeIs('master.*', 'accounts.*') ? 'active' : '' }}" href="{{ route('master.index') }}"><x-icon name="database" />Master data</a>
                </div>
            @endif

            @if (auth()->user()->role === \App\Enums\UserRole::SuperAdmin)
                <p class="sidebar-label">Sistem</p>
                <div class="space-y-1">
                    <a class="nav-link {{ request()->routeIs('cms.*') ? 'active' : '' }}" href="{{ route('cms.index') }}"><x-icon name="globe" />Konten website</a>
                    <a class="nav-link {{ request()->routeIs('audit.*') ? 'active' : '' }}" href="{{ route('audit.index') }}"><x-icon name="shield" />Audit aktivitas</a>
                </div>
            @endif
        </nav>

        <div class="rounded-2xl border border-white/[.08] bg-white/[.05] p-3.5">
            <div class="flex items-center gap-3">
                <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-white/10 text-sm font-extrabold text-teal-200">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                <div class="min-w-0"><p class="truncate text-sm font-bold text-white">{{ auth()->user()->name }}</p><p class="mt-0.5 truncate text-[11px] font-medium text-slate-400">{{ auth()->user()->role->label() }}</p></div>
            </div>
        </div>
    </aside>

    <div class="min-h-screen lg:pl-72">
        <header class="app-topbar">
            <div class="flex h-[4.5rem] items-center gap-3 px-4 sm:px-6 lg:px-8">
                <button data-nav-toggle class="btn btn-icon btn-secondary lg:hidden" type="button" aria-label="Buka menu" aria-expanded="false"><x-icon name="menu" /></button>
                <div class="min-w-0">
                    <p class="truncate text-sm font-extrabold text-slate-900">@yield('title', 'Dashboard')</p>
                    <p class="hidden text-xs text-slate-400 sm:block">{{ now()->translatedFormat('l, d F Y') }}</p>
                </div>
                <div class="ml-auto flex items-center gap-2 sm:gap-3">
                    <span class="hidden items-center gap-2 rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700 ring-1 ring-inset ring-emerald-100 md:inline-flex"><span class="size-1.5 rounded-full bg-emerald-500"></span>Sistem aktif</span>
                    <form method="post" action="{{ route('logout') }}">@csrf<button class="btn btn-secondary" title="Keluar dari aplikasi"><x-icon name="logout" /><span class="hidden sm:inline">Keluar</span></button></form>
                </div>
            </div>
        </header>

        <main class="px-4 py-6 sm:px-6 sm:py-7 lg:px-8 lg:py-8">
            <div class="app-content">
                @if (session('success'))<div class="alert alert-success"><x-icon name="check" class="mt-0.5 size-5 shrink-0" /><div>{{ session('success') }}</div></div>@endif
                @if (session('warning'))<div class="alert alert-warning"><x-icon name="alert" class="mt-0.5 size-5 shrink-0" /><div>{{ session('warning') }}</div></div>@endif
                @if ($errors->any())<div class="alert alert-danger"><x-icon name="alert" class="mt-0.5 size-5 shrink-0" /><div><strong>Periksa kembali isian:</strong><ul class="mt-2 list-disc space-y-1 pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div>@endif
                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>
