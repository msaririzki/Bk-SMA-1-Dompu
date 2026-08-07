@extends('layouts.app')

@section('title', 'Review Impor')

@section('content')
    <header class="page-header">
        <div>
            <a class="back-link" href="{{ route('imports.index') }}"><x-icon name="arrow-left" /> Riwayat impor</a>
            <p class="page-eyebrow"><x-icon name="upload" class="size-4" /> Validasi data</p>
            <h1 class="page-title">Review {{ $batch->original_name }}</h1>
            <p class="page-description">Konfirmasi hanya memasukkan baris siap dan pembaruan identitas resmi.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a class="btn btn-secondary" href="{{ route('imports.report', $batch) }}">Unduh laporan konflik</a>
            @if ($batch->status !== 'committed')
                <form method="post" action="{{ route('imports.commit', $batch) }}" onsubmit="return confirm('Impor semua baris siap?')">
                    @csrf
                    <button class="btn btn-primary">Konfirmasi {{ $batch->ready_rows }} baris</button>
                </form>
            @endif
        </div>
    </header>

    <div class="grid gap-4 sm:grid-cols-4">
        <div class="stat"><p class="stat-label">Total</p><p class="stat-value">{{ $batch->total_rows }}</p></div>
        <div class="stat"><p class="stat-label">Siap</p><p class="stat-value text-emerald-600">{{ $batch->ready_rows }}</p></div>
        <div class="stat"><p class="stat-label">Konflik</p><p class="stat-value text-orange-600">{{ $batch->conflict_rows }}</p></div>
        <div class="stat"><p class="stat-label">Diimpor</p><p class="stat-value">{{ $batch->imported_rows }}</p></div>
    </div>

    <form data-auto-filter-form class="card card-body mt-6 grid gap-3 md:grid-cols-[1fr_220px_180px_auto]">
        <div><input class="form-control" name="q" value="{{ request('q') }}" placeholder="Cari nama/identitas"><p class="field-help">Pencarian berjalan 2 detik setelah selesai mengetik.</p></div>
        <select class="form-select" name="sheet">
            <option value="">Semua sheet</option>
            @foreach ($sheets as $sheet)
                <option value="{{ $sheet }}" @selected(request('sheet') === $sheet)>{{ $sheet }}</option>
            @endforeach
        </select>
        <select class="form-select" name="status">
            <option value="">Semua status</option>
            @foreach (\App\Enums\ImportRowStatus::cases() as $status)
                <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->value }}</option>
            @endforeach
        </select>
        <button class="btn btn-secondary">Terapkan filter</button>
    </form>

    <div class="card table-wrap mt-4">
        <table class="table">
            <thead>
                <tr><th>Sheet/baris</th><th>Nama</th><th>NISN / NIS</th><th>Kelas</th><th>Status</th><th>Keterangan/review</th></tr>
            </thead>
            <tbody>
                @foreach ($rows as $row)
                    <tr>
                        <td>{{ $row->sheet_name }}:{{ $row->row_number }}</td>
                        <td>
                            <strong>{{ $row->normalized_payload['name'] ?? '-' }}</strong>
                            <p class="text-xs text-slate-400">{{ $row->normalized_payload['gender'] ?? '' }}</p>
                        </td>
                        <td>{{ $row->normalized_payload['nisn'] ?? '-' }}<br><span class="text-xs text-slate-400">{{ $row->normalized_payload['nis'] ?? '-' }}</span></td>
                        <td>{{ $row->normalized_payload['class_name'] ?? '-' }}</td>
                        <td><span class="badge {{ $row->status->value === 'conflict' ? 'badge-orange' : ($row->status->value === 'imported' ? 'badge-emerald' : 'badge-slate') }}">{{ $row->status->value }}</span></td>
                        <td class="max-w-sm text-xs">
                            <p>{{ $row->message }}</p>
                            @if ($row->matchedStudent)
                                <div class="mt-2 rounded-xl border border-teal-100 bg-teal-50 p-3 text-teal-800"><strong class="block">Calon siswa yang sudah ada</strong><span class="mt-1 block">{{ $row->matchedStudent->name }} · {{ $row->matchedStudent->nisn ?: ($row->matchedStudent->nis ?: $row->matchedStudent->temporary_id) }}</span></div>
                            @endif
                            @if ($row->status === \App\Enums\ImportRowStatus::Conflict && $batch->status !== 'committed')
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @unless ($row->normalized_payload['reference_only'] ?? false)
                                        <form method="post" action="{{ route('imports.resolve', [$batch, $row]) }}">
                                            @csrf
                                            <input type="hidden" name="decision" value="accept">
                                            <button class="btn btn-accent px-3 py-1.5 text-xs">{{ $row->matchedStudent ? 'Perbarui siswa ini' : 'Gunakan sheet kelas' }}</button>
                                        </form>
                                    @endunless
                                    <form method="post" action="{{ route('imports.resolve', [$batch, $row]) }}">
                                        @csrf
                                        <input type="hidden" name="decision" value="ignore">
                                        <button class="btn btn-secondary px-3 py-1.5 text-xs">Abaikan baris</button>
                                    </form>
                                </div>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-5">{{ $rows->links() }}</div>

    @if (auth()->user()->role === \App\Enums\UserRole::SuperAdmin && $batch->status === 'review')
        <section id="batalkan-review" class="card mt-8 border-red-200">
            <div class="card-header"><div><h2 class="section-title text-red-700">Batalkan file tahap review</h2><p class="section-description">Hanya menghapus file Excel dan hasil analisis yang belum dikonfirmasi. Data siswa, kelas, kasus, dan riwayat pelanggaran tidak ikut dihapus.</p></div><span class="badge badge-red">Super Admin</span></div>
            <form method="post" action="{{ route('imports.destroy', $batch) }}" class="card-body grid items-end gap-4 lg:grid-cols-[minmax(0,1fr)_220px_auto]" onsubmit="return confirm('Batalkan file review ini? Data siswa tidak akan dihapus.')">
                @csrf
                @method('delete')
                <div><label class="form-label" for="discard-password">Kata sandi Super Admin</label><input id="discard-password" class="form-control" type="password" name="password" required autocomplete="current-password" placeholder="Masukkan kata sandi akun"></div>
                <div><label class="form-label" for="discard-confirmation">Ketik HAPUS</label><input id="discard-confirmation" class="form-control" name="confirmation" required autocomplete="off" placeholder="HAPUS"></div>
                <button class="btn btn-danger"><x-icon name="trash" /> Batalkan file review</button>
            </form>
        </section>
    @endif
@endsection
