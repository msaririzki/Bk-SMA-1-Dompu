@extends('layouts.app')

@section('title', $student->name)

@section('content')
    <header class="page-header">
        <div>
            <a class="back-link" href="{{ route('students.index') }}"><x-icon name="arrow-left" /> Data siswa</a>
            <p class="page-eyebrow"><x-icon name="user" class="size-4" /> Profil siswa</p>
            <h1 class="page-title">{{ $student->name }}</h1>
            <p class="page-description">{{ $student->temporary_id }} · {{ $student->currentEnrollment?->schoolClass?->name ?? 'Belum ada kelas' }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a class="btn btn-secondary" href="{{ route('students.recap', $student) }}">Unduh rekap PDF</a>
            @can('update', $student)
                <a class="btn btn-accent" href="{{ route('cases.create', ['student' => $student->id]) }}">+ Pelanggaran</a>
            @endcan
        </div>
    </header>

    @if ($score)
        <div class="grid gap-4 sm:grid-cols-3">
            <div class="stat"><p class="stat-label">Poin {{ $year->name }}</p><p class="stat-value">{{ $score['annual_points'] }}</p></div>
            <div class="stat">
                <p class="stat-label">Progres ambang</p><p class="stat-value">{{ $score['percentage'] }}%</p>
                <div class="mt-3 h-2 rounded-full bg-slate-100"><div class="h-2 rounded-full bg-teal-500" style="width:{{ $score['percentage'] }}%"></div></div>
            </div>
            <div class="stat">
                <p class="stat-label">Tingkat</p>
                <p class="mt-3"><span class="badge badge-{{ $score['severity']?->color ?? 'slate' }}">{{ $score['severity']?->name ?? 'Tidak ada pelanggaran' }}</span></p>
                <p class="mt-3 text-xs text-slate-500">Total seluruh riwayat: {{ $score['all_time_points'] }} poin</p>
            </div>
        </div>
    @endif

    <div class="mt-6 grid gap-6 xl:grid-cols-[.7fr_1.3fr]">
        <section class="card card-body">
            <h2 class="text-lg font-bold">Identitas</h2>
            @can('update', $student)
                <form method="post" action="{{ route('students.update', $student) }}" class="mt-5 space-y-4">
                    @csrf
                    @method('put')
                    <div><label class="form-label">Nama lengkap</label><input class="form-control" name="name" value="{{ old('name', $student->name) }}" required></div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div><label class="form-label">NIS</label><input class="form-control" name="nis" value="{{ old('nis', $student->nis) }}"></div>
                        <div><label class="form-label">NISN</label><input class="form-control" name="nisn" value="{{ old('nisn', $student->nisn) }}"></div>
                        <div><label class="form-label">Jenis kelamin</label><select class="form-select" name="gender"><option value="L" @selected($student->gender === 'L')>Laki-laki</option><option value="P" @selected($student->gender === 'P')>Perempuan</option></select></div>
                        <div><label class="form-label">Status</label><select class="form-select" name="status">@foreach (\App\Enums\StudentStatus::cases() as $status)<option value="{{ $status->value }}" @selected($student->status === $status)>{{ $status->label() }}</option>@endforeach</select></div>
                    </div>
                    <button class="btn btn-primary">Simpan identitas</button>
                </form>
            @else
                <dl class="mt-5 grid gap-4 text-sm sm:grid-cols-2">
                    <div><dt class="form-label">Nama lengkap</dt><dd>{{ $student->name }}</dd></div>
                    <div><dt class="form-label">NIS</dt><dd>{{ $student->nis ?: '-' }}</dd></div>
                    <div><dt class="form-label">NISN</dt><dd>{{ $student->nisn ?: '-' }}</dd></div>
                    <div><dt class="form-label">Jenis kelamin</dt><dd>{{ $student->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</dd></div>
                    <div><dt class="form-label">Status</dt><dd>{{ $student->status->label() }}</dd></div>
                </dl>
                <p class="mt-5 rounded-xl bg-slate-50 p-3 text-xs text-slate-500">Data ini hanya dapat diubah oleh guru BK pembina kelas, koordinator, atau super admin.</p>
            @endcan

            <div class="mt-8 border-t border-slate-200 pt-5">
                <h3 class="font-bold">Riwayat kelas</h3>
                <div class="mt-3 space-y-2">
                    @foreach ($student->enrollments as $enrollment)
                        <div class="flex justify-between rounded-xl bg-slate-50 px-3 py-2 text-sm"><span>{{ $enrollment->schoolClass->name }}</span><span class="text-slate-500">{{ $enrollment->academicYear->name ?? '' }}</span></div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="card">
            <div class="card-body"><h2 class="text-lg font-bold">Timeline pelanggaran</h2><p class="text-sm text-slate-500">Riwayat terbaru ditampilkan lebih dahulu.</p></div>
            <div class="divide-y divide-slate-100">
                @forelse ($student->cases as $case)
                    <a href="{{ route('cases.show', $case) }}" class="block p-5 hover:bg-slate-50">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div><strong>{{ $case->case_number }}</strong><p class="mt-1 text-sm text-slate-500">{{ $case->occurred_at->translatedFormat('d F Y, H:i') }} · {{ $case->location ?: 'Lokasi tidak dicatat' }}</p></div>
                            <span class="badge badge-slate">{{ $case->status->label() }}</span>
                        </div>
                        <p class="mt-3 line-clamp-2 text-sm text-slate-600">{{ $case->chronology }}</p>
                        <div class="mt-3 flex flex-wrap gap-2">@foreach ($case->items as $item)<span class="badge badge-amber">{{ $item->instrument_code }} · {{ $item->points }} poin</span>@endforeach</div>
                    </a>
                @empty
                    <p class="p-8 text-center text-sm text-slate-400">Belum ada catatan pelanggaran.</p>
                @endforelse
            </div>
        </section>
    </div>
@endsection
