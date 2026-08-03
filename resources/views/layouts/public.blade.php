<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#061423">
    <meta name="description" content="Sistem Informasi Bimbingan dan Konseling SMAN 1 Dompu">
    <title>@yield('title', 'SMAN 1 Dompu') — Sistem Informasi BK</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="public-shell">
    <header class="sticky top-0 z-40 border-b border-white/10 bg-navy-950/90 text-white shadow-lg shadow-navy-950/5 backdrop-blur-xl">
        <div class="container-page relative flex h-[4.75rem] items-center justify-between">
            <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-3">
                @if ($schoolLogo = \App\Models\SchoolSetting::value('school_logo'))
                    <img class="size-11 rounded-2xl bg-white object-contain p-1.5 shadow-lg" src="{{ Storage::disk('public')->url($schoolLogo) }}" alt="Logo sekolah">
                @else
                    <span class="grid size-11 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-teal-400 to-teal-600 font-black text-navy-950 shadow-lg shadow-teal-500/20">BK</span>
                @endif
                <span class="min-w-0"><strong class="block truncate leading-tight text-white">{{ \App\Models\SchoolSetting::value('school_name', 'SMAN 1 Dompu') }}</strong><small class="mt-0.5 block text-xs font-medium text-slate-400">Sistem Informasi BK</small></span>
            </a>
            <button data-public-nav-toggle class="btn btn-icon border border-white/15 bg-white/5 text-white hover:bg-white/10 md:hidden" type="button" aria-label="Buka navigasi" aria-expanded="false"><x-icon name="menu" /></button>
            <nav data-public-nav class="absolute left-4 right-4 top-[4.3rem] hidden flex-col gap-1 rounded-2xl border border-white/10 bg-navy-900/95 p-3 text-sm shadow-2xl backdrop-blur-xl md:static md:flex md:flex-row md:items-center md:gap-1 md:border-0 md:bg-transparent md:p-0 md:shadow-none">
                <a class="public-nav-link {{ request()->routeIs('public.profile') ? 'active' : '' }}" href="{{ route('public.profile') }}">Profil</a>
                <a class="public-nav-link {{ request()->routeIs('public.rules') ? 'active' : '' }}" href="{{ route('public.rules') }}">Tata tertib</a>
                <a class="public-nav-link {{ request()->routeIs('public.etiquette') ? 'active' : '' }}" href="{{ route('public.etiquette') }}">Tata krama</a>
                <a class="public-nav-link {{ request()->routeIs('public.bk') ? 'active' : '' }}" href="{{ route('public.bk') }}">Informasi BK</a>
                <a class="btn btn-accent mt-2 md:ml-3 md:mt-0" href="{{ route('login') }}"><x-icon name="lock" />Masuk</a>
            </nav>
        </div>
    </header>
    <main>@yield('content')</main>
    <footer class="mt-20 border-t border-white/5 bg-navy-950 py-12 text-slate-400">
        <div class="container-page grid gap-10 md:grid-cols-[1.2fr_.8fr_.8fr]">
            <div>
                <div class="flex items-center gap-3"><span class="grid size-10 place-items-center rounded-xl bg-teal-500 font-black text-navy-950">BK</span><strong class="text-white">{{ \App\Models\SchoolSetting::value('school_name', 'SMAN 1 Dompu') }}</strong></div>
                <p class="mt-4 max-w-sm text-sm leading-6">{{ \App\Models\SchoolSetting::value('tagline', 'Berkarakter, Cerdas dan Berbudaya') }}. Layanan pendampingan yang tertata, aman, dan berpihak kepada siswa.</p>
            </div>
            <div><p class="text-xs font-bold uppercase tracking-[.16em] text-slate-500">Informasi</p><div class="mt-4 space-y-3 text-sm"><a class="block hover:text-teal-300" href="{{ route('public.profile') }}">Profil sekolah</a><a class="block hover:text-teal-300" href="{{ route('public.rules') }}">Tata tertib</a><a class="block hover:text-teal-300" href="{{ route('public.etiquette') }}">Tata krama</a></div></div>
            <div><p class="text-xs font-bold uppercase tracking-[.16em] text-slate-500">Akses</p><div class="mt-4 space-y-3 text-sm"><a class="block hover:text-teal-300" href="{{ route('login') }}">Portal staf BK</a><a class="block hover:text-teal-300" href="{{ route('student.login') }}">Portal siswa</a><p class="flex items-center gap-2 pt-2 text-xs text-emerald-400"><span class="size-1.5 rounded-full bg-emerald-400"></span>Data dilindungi sesuai hak akses</p></div></div>
        </div>
        <div class="container-page mt-10 border-t border-white/[.07] pt-6 text-xs">© {{ date('Y') }} Sistem Informasi BK SMAN 1 Dompu</div>
    </footer>
</body>
</html>
