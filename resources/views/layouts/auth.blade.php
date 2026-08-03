<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#061423">
    <title>@yield('title') — Sistem BK</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="auth-shell">
    <div class="grid min-h-screen lg:grid-cols-[1.05fr_.95fr]">
        <section class="auth-panel relative hidden overflow-hidden p-10 text-white lg:flex lg:flex-col xl:p-14">
            <div class="hero-grid pointer-events-none absolute inset-0 opacity-70"></div>
            <div class="relative z-10 flex items-center gap-3"><span class="grid size-11 place-items-center rounded-2xl bg-teal-400 font-black text-navy-950 shadow-lg shadow-teal-500/20">BK</span><div><strong class="block text-white">SMAN 1 Dompu</strong><span class="text-xs text-slate-400">Sistem Informasi Bimbingan & Konseling</span></div></div>
            <div class="relative z-10 my-auto max-w-xl py-12">
                <span class="inline-flex items-center gap-2 rounded-full border border-teal-300/15 bg-teal-300/10 px-3 py-1.5 text-xs font-bold text-teal-200"><span class="size-1.5 rounded-full bg-teal-300"></span>Ruang kerja digital guru BK</span>
                <h1 class="mt-7 text-5xl font-extrabold leading-[1.08] tracking-[-.045em] text-white xl:text-6xl">Administrasi lebih rapi. Pendampingan lebih berarti.</h1>
                <p class="mt-6 max-w-lg text-lg leading-8 text-slate-300">Simpan riwayat, tindak lanjut, dokumen, dan home visit dalam satu sistem yang aman serta mudah digunakan.</p>
                <div class="mt-9 grid max-w-lg grid-cols-2 gap-3">
                    @foreach ([['shield', 'Akses terlindungi'], ['documents', 'Arsip terpusat'], ['chart', 'Prioritas terukur'], ['home', 'Home visit tercatat']] as [$icon, $label])
                        <div class="flex items-center gap-3 rounded-2xl border border-white/[.08] bg-white/[.05] p-3.5 text-sm font-semibold text-slate-200 backdrop-blur"><span class="grid size-9 place-items-center rounded-xl bg-teal-400/10 text-teal-300"><x-icon :name="$icon" class="size-4" /></span>{{ $label }}</div>
                    @endforeach
                </div>
            </div>
            <p class="relative z-10 text-xs text-slate-500">SMAN 1 Dompu · Berkarakter, Cerdas dan Berbudaya</p>
        </section>
        <section class="flex min-h-screen items-center justify-center px-5 py-10 sm:px-8 lg:py-12">
            <div class="w-full max-w-md">
                <a href="{{ route('home') }}" class="mb-7 flex items-center justify-center gap-2 text-sm font-bold text-slate-600 lg:justify-start"><span class="grid size-9 place-items-center rounded-xl bg-navy-900 text-xs font-black text-white lg:hidden">BK</span><span class="lg:hidden">SMAN 1 Dompu</span></a>
                @yield('content')
                <a href="{{ route('home') }}" class="mt-6 flex items-center justify-center gap-2 text-sm font-semibold text-slate-500 transition hover:text-teal-700"><x-icon name="arrow-left" class="size-4" />Kembali ke beranda</a>
            </div>
        </section>
    </div>
</body>
</html>
