<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Sistem Informasi Bimbingan dan Konseling SMAN 1 Dompu">
    <title>@yield('title', 'SMAN 1 Dompu') — Sistem Informasi BK</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body>
<header class="sticky top-0 z-40 border-b border-white/10 bg-navy-950/95 text-white backdrop-blur">
    <div class="container-page relative flex h-18 items-center justify-between">
        <a href="{{ route('home') }}" class="flex items-center gap-3">
            @if($schoolLogo=\App\Models\SchoolSetting::value('school_logo'))<img class="size-10 rounded-xl bg-white object-contain p-1" src="{{ Storage::disk('public')->url($schoolLogo) }}" alt="Logo sekolah">@else<span class="grid size-10 place-items-center rounded-xl bg-teal-500 font-black">BK</span>@endif
            <span><strong class="block leading-tight">{{ \App\Models\SchoolSetting::value('school_name','SMAN 1 Dompu') }}</strong><small class="text-slate-300">Sistem Informasi BK</small></span>
        </a>
        <button data-public-nav-toggle class="btn border border-white/20 text-white md:hidden" type="button" aria-expanded="false">Menu</button>
        <nav data-public-nav class="absolute left-4 right-4 top-16 hidden flex-col gap-2 rounded-2xl border border-white/10 bg-navy-900 p-3 text-sm shadow-xl md:static md:flex md:flex-row md:items-center md:gap-6 md:border-0 md:bg-transparent md:p-0 md:shadow-none">
            <a class="hover:text-teal-300" href="{{ route('public.profile') }}">Profil</a>
            <a class="hover:text-teal-300" href="{{ route('public.rules') }}">Tata tertib</a>
            <a class="hover:text-teal-300" href="{{ route('public.etiquette') }}">Tata krama</a>
            <a class="hover:text-teal-300" href="{{ route('public.bk') }}">Informasi BK</a>
            <a class="btn btn-accent" href="{{ route('login') }}">Masuk</a>
        </nav>
    </div>
</header>
<main>@yield('content')</main>
<footer class="mt-16 bg-navy-950 py-10 text-slate-300"><div class="container-page flex flex-col justify-between gap-4 sm:flex-row"><div><strong class="text-white">{{ \App\Models\SchoolSetting::value('school_name','SMAN 1 Dompu') }}</strong><p class="mt-1 text-sm">{{ \App\Models\SchoolSetting::value('tagline','Berkarakter, Cerdas dan Berbudaya') }}</p></div><p class="text-sm">© {{ date('Y') }} Sistem Informasi BK</p></div></footer>
</body>
</html>
