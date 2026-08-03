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

    <form class="card card-body mt-6 grid gap-3 md:grid-cols-[1fr_220px_180px_auto]">
        <input class="form-control" name="q" value="{{ request('q') }}" placeholder="Cari nama/identitas">
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
                            @if ($row->status === \App\Enums\ImportRowStatus::Conflict && $batch->status !== 'committed')
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @unless ($row->normalized_payload['reference_only'] ?? false)
                                        <form method="post" action="{{ route('imports.resolve', [$batch, $row]) }}">
                                            @csrf
                                            <input type="hidden" name="decision" value="accept">
                                            <button class="btn btn-accent px-3 py-1.5 text-xs">Gunakan sheet kelas</button>
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
@endsection
