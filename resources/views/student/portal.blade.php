<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>Data Saya — Sistem BK</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="public-shell">
    <header class="sticky top-0 z-30 border-b border-white/10 bg-navy-950/95 text-white shadow-lg shadow-navy-950/10 backdrop-blur-xl">
        <div class="container-page flex min-h-18 items-center justify-between gap-4 py-3">
            <a class="flex items-center gap-3" href="{{ route('student.portal') }}"><span class="grid size-10 place-items-center rounded-2xl bg-gradient-to-br from-teal-400 to-teal-600 shadow-lg shadow-teal-950/30"><x-icon name="students" class="size-5" /></span><span><span class="block text-sm font-extrabold">Portal Siswa</span><span class="block text-[11px] text-slate-400">SMAN 1 Dompu</span></span></a>
            <form method="post" action="{{ route('logout') }}">@csrf<button class="btn border border-white/15 bg-white/5 text-white hover:bg-white/10" aria-label="Keluar dari portal siswa"><x-icon name="logout" /> <span class="hidden sm:inline">Keluar</span></button></form>
        </div>
    </header>
    <main class="pb-14">
        <section class="relative overflow-hidden bg-navy-950 text-white">
            <div class="hero-grid absolute inset-0"></div><div class="absolute -right-20 -top-24 size-72 rounded-full bg-teal-500/15 blur-3xl"></div>
            <div class="container-page relative py-10 sm:py-14">
                @if(session('success'))<div class="alert alert-success mb-6 max-w-2xl"><x-icon name="check" class="size-5 shrink-0" /><span>{{ session('success') }}</span></div>@endif
                <p class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-[.15em] text-teal-300"><span class="size-1.5 rounded-full bg-teal-300"></span>{{ $student->currentEnrollment?->schoolClass?->name ?? 'Kelas belum ditentukan' }}</p>
                <h1 class="mt-3 text-3xl font-extrabold tracking-tight text-white sm:text-4xl">Halo, {{ $student->name }}</h1>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-300">Pantau ringkasan poin dan status tindak lanjut Anda. Informasi pada halaman ini bersifat pribadi.</p>
            </div>
        </section>
        <div class="container-page -mt-5 relative z-10">
            @if($score)
                <div class="grid gap-4 sm:grid-cols-3">
                    <div class="stat"><span class="stat-icon"><x-icon name="chart" /></span><p class="stat-label">Total poin {{ $year->name }}</p><p class="stat-value">{{ $score['annual_points'] }}</p><p class="mt-2 text-xs text-slate-400">Akumulasi tahun berjalan</p></div>
                    <div class="stat stat-blue"><span class="stat-icon"><x-icon name="scale" /></span><p class="stat-label">Progres ambang</p><div class="flex items-end justify-between gap-3"><p class="stat-value">{{ $score['percentage'] }}%</p><span class="mb-1 text-xs font-semibold text-slate-400">dari ambang</span></div><div class="mt-4 h-2 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-gradient-to-r from-teal-600 to-cyan-400" style="width: {{ $score['percentage'] }}%"></div></div></div>
                    <div class="stat stat-warm"><span class="stat-icon"><x-icon name="shield" /></span><p class="stat-label">Tingkat saat ini</p><p class="mt-4"><span class="badge badge-{{ $score['severity']?->color ?? 'emerald' }}">{{ $score['severity']?->name ?? 'Tidak ada pelanggaran' }}</span></p><p class="mt-3 text-xs text-slate-400">Berdasarkan total poin tahunan</p></div>
                </div>
            @endif
            <section class="card mt-6 overflow-hidden">
                <div class="card-header"><div class="flex items-center gap-3"><span class="feature-icon"><x-icon name="clipboard" /></span><div><h2 class="section-title">Riwayat saya</h2><p class="section-description">Catatan konseling, foto, dan dokumen internal tidak ditampilkan.</p></div></div><span class="badge badge-slate">{{ $student->cases->count() }} catatan</span></div>
                <div class="divide-y divide-slate-100">
                    @forelse($student->cases as $case)
                        <div class="p-5 transition hover:bg-slate-50/60 sm:p-6"><div class="flex flex-col justify-between gap-4 sm:flex-row"><div class="flex gap-4"><span class="grid size-11 shrink-0 place-items-center rounded-2xl bg-slate-100 text-slate-600"><x-icon name="calendar" class="size-5" /></span><div><strong class="text-slate-900">{{ $case->occurred_at->translatedFormat('d F Y') }}</strong><p class="mt-1 max-w-2xl text-sm leading-6 text-slate-500">{{ $case->items->pluck('instrument_name')->join(', ') }}</p></div></div><div class="flex items-center justify-between gap-3 sm:block sm:text-right"><span class="badge badge-amber">{{ $case->items->sum('points') }} poin</span><p class="mt-0 text-xs font-semibold text-slate-400 sm:mt-2">{{ $case->status->label() }}</p></div></div></div>
                    @empty
                        <div class="empty-state py-14"><span class="mx-auto mb-4 grid size-12 place-items-center rounded-2xl bg-emerald-50 text-emerald-600"><x-icon name="check" class="size-6" /></span><p class="font-bold text-slate-600">Tidak ada catatan pelanggaran pada tahun ini.</p></div>
                    @endforelse
                </div>
            </section>
            <div class="mt-6 flex items-start gap-3 rounded-2xl border border-slate-200 bg-white/70 p-4 text-xs leading-5 text-slate-500"><x-icon name="lock" class="mt-0.5 size-4 shrink-0 text-teal-700" /><p>Selalu keluar setelah selesai, terutama saat menggunakan perangkat bersama, agar data Anda tidak dilihat orang lain.</p></div>
        </div>
    </main>
</body>
</html>
