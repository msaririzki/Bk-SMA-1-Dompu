@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<header class="page-header">
    <div>
        <p class="page-eyebrow"><x-icon name="calendar" class="size-4" /> Tahun pelajaran {{ $year?->name ?? 'belum aktif' }}</p>
        <h1 class="page-title">Dashboard BK</h1>
        <p class="page-description">Pantau prioritas penanganan, agenda, dan administrasi sekolah dari satu tempat.</p>
    </div>
    <a class="btn btn-primary" href="{{ route('cases.create') }}"><x-icon name="plus" /> Catat pelanggaran</a>
</header>

<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <div class="stat"><span class="stat-icon"><x-icon name="students" /></span><p class="stat-label">Siswa aktif</p><p class="stat-value">{{ number_format($students) }}</p><p class="mt-2 text-xs text-slate-400">Terdaftar pada sistem</p></div>
    <div class="stat stat-blue"><span class="stat-icon"><x-icon name="calendar" /></span><p class="stat-label">Kasus hari ini</p><p class="stat-value">{{ $todayCases }}</p><p class="mt-2 text-xs text-slate-400">Kejadian tercatat</p></div>
    <div class="stat stat-violet"><span class="stat-icon"><x-icon name="chart" /></span><p class="stat-label">Kasus bulan ini</p><p class="stat-value">{{ $monthCases }}</p><p class="mt-2 text-xs text-slate-400">Periode berjalan</p></div>
    <div class="stat stat-warm"><span class="stat-icon"><x-icon name="clipboard" /></span><p class="stat-label">Belum selesai</p><p class="stat-value text-orange-600">{{ $openCases }}</p><p class="mt-2 text-xs text-slate-400">Perlu tindak lanjut</p></div>
</div>

<div class="mt-6 grid gap-6 xl:grid-cols-[1.35fr_.65fr]">
    <section class="card overflow-hidden">
        <div class="card-header">
            <div><h2 class="section-title">Siswa prioritas</h2><p class="section-description">Berdasarkan total poin pada tahun pelajaran aktif.</p></div>
            <a class="btn btn-ghost" href="{{ route('students.index') }}">Lihat semua <x-icon name="arrow-right" /></a>
        </div>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Siswa</th><th>Kelas</th><th class="text-right">Poin</th></tr></thead>
                <tbody>
                    @forelse($priority as $student)
                        <tr>
                            <td><a class="font-bold text-navy-900 transition hover:text-teal-700" href="{{ route('students.show', $student) }}">{{ $student->name }}</a><p class="mt-1 text-xs text-slate-400">{{ $student->nisn ?: ($student->nis ?: $student->temporary_id) }}</p></td>
                            <td>{{ $student->currentEnrollment?->schoolClass?->name ?? '-' }}</td>
                            <td class="text-right"><span class="badge {{ $student->annual_points >= 75 ? 'badge-red' : ($student->annual_points >= 50 ? 'badge-orange' : 'badge-amber') }}">{{ $student->annual_points }} poin</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="3"><div class="empty-state"><x-icon name="check" class="mx-auto mb-3 size-8 text-emerald-500" /><p>Belum ada pelanggaran pada tahun aktif.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <div class="space-y-6">
        <section class="card card-body">
            <div class="flex items-center gap-3"><span class="feature-icon size-10"><x-icon name="chart" class="size-5" /></span><div><h2 class="section-title">Pelanggaran terbanyak</h2><p class="text-xs text-slate-400">Jenis yang sering dicatat</p></div></div>
            <div class="mt-6 space-y-5">
                @forelse($topInstruments as $item)
                    <div><div class="flex justify-between gap-4 text-sm"><span class="line-clamp-1 font-semibold text-slate-700">{{ $item->name }}</span><strong>{{ $item->total }}</strong></div><div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-gradient-to-r from-teal-600 to-cyan-400" style="width: {{ min(100, $item->total * 10) }}%"></div></div></div>
                @empty
                    <div class="empty-state px-0"><p>Belum ada data.</p></div>
                @endforelse
            </div>
        </section>
        <section class="card card-body">
            <div class="flex items-center gap-3"><span class="feature-icon size-10"><x-icon name="calendar" class="size-5" /></span><div><h2 class="section-title">Agenda mendatang</h2><p class="text-xs text-slate-400">Tindak lanjut terjadwal</p></div></div>
            <div class="mt-5 space-y-3">
                @forelse($followUps as $follow)
                    <div class="card-muted flex items-center justify-between gap-3"><div><strong class="text-sm text-slate-900">{{ $follow->type->label() }}</strong><p class="mt-1 text-xs text-slate-500">{{ $follow->scheduled_at?->translatedFormat('d M Y') ?? 'Belum dijadwalkan' }}</p></div><span class="size-2 rounded-full bg-teal-500 ring-4 ring-teal-50"></span></div>
                @empty
                    <p class="py-4 text-center text-sm text-slate-400">Tidak ada agenda.</p>
                @endforelse
            </div>
        </section>
    </div>
</div>

@if(auth()->user()->role === \App\Enums\UserRole::SuperAdmin)
    <section class="card card-body mt-6">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div class="flex items-start gap-4"><span class="feature-icon"><x-icon name="database" /></span><div><h2 class="section-title">Status backup</h2><p class="section-description">Database dan arsip privat terenkripsi pada penyimpanan luar VPS.</p></div></div>
            @if($backupStatus)
                <div class="text-left sm:text-right"><span class="badge {{ ($backupStatus['successful'] ?? false) ? 'badge-emerald' : 'badge-red' }}">{{ ($backupStatus['successful'] ?? false) ? 'Berhasil' : 'Gagal' }}</span><p class="mt-2 text-xs text-slate-500">{{ \Illuminate\Support\Carbon::parse($backupStatus['finished_at'])->translatedFormat('d M Y H:i') }}</p></div>
            @else
                <span class="badge badge-amber">Belum pernah dijalankan</span>
            @endif
        </div>
    </section>
@endif

<div class="mt-6 grid gap-6 xl:grid-cols-3">
    <section class="card card-body"><div class="flex items-center gap-3"><span class="feature-icon size-10"><x-icon name="scale" class="size-5" /></span><h2 class="section-title">Tingkat prioritas</h2></div><div class="mt-5 space-y-2.5">@foreach($severityCounts as $level)<div class="flex items-center justify-between rounded-2xl border border-slate-100 bg-slate-50/70 px-4 py-3 text-sm"><span class="font-semibold">{{ $level['name'] }}</span><span class="badge badge-{{ $level['color'] }}">{{ $level['total'] }} siswa</span></div>@endforeach</div></section>
    <section class="card card-body"><div class="flex items-center gap-3"><span class="feature-icon size-10"><x-icon name="students" class="size-5" /></span><h2 class="section-title">Distribusi kelas</h2></div><div class="mt-5 max-h-72 space-y-1 overflow-y-auto pr-1">@foreach($classDistribution as $class)<div class="flex justify-between rounded-xl px-3 py-2.5 text-sm transition hover:bg-slate-50"><span>{{ $class->name }}</span><strong>{{ $class->total }}</strong></div>@endforeach</div></section>
    <section class="card card-body"><div class="flex items-center gap-3"><span class="feature-icon size-10"><x-icon name="clipboard" class="size-5" /></span><h2 class="section-title">Aktivitas terbaru</h2></div><div class="mt-5 space-y-5">@forelse($recentActivities as $activity)<div class="timeline-dot"><strong class="text-sm text-slate-900">{{ $activity->user?->name ?? 'Sistem' }}</strong><p class="mt-0.5 text-sm text-slate-500">{{ $activity->action }}</p><p class="mt-1 text-xs text-slate-400">{{ $activity->created_at->diffForHumans() }}</p></div>@empty<p class="py-4 text-center text-sm text-slate-400">Belum ada aktivitas.</p>@endforelse</div></section>
</div>
@endsection
