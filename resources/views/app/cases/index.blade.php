@extends('layouts.app')
@section('title', 'Pelanggaran')
@section('content')
<header class="page-header">
    <div><p class="page-eyebrow"><x-icon name="clipboard" class="size-4" /> Manajemen kasus</p><h1 class="page-title">Pencatatan pelanggaran</h1><p class="page-description">Seluruh kejadian, poin, bukti, dan tindak lanjut tersimpan dalam satu riwayat.</p></div>
    <a class="btn btn-primary" href="{{ route('cases.create') }}"><x-icon name="plus" /> Catat pelanggaran</a>
</header>
<form data-auto-filter-form class="card card-body mb-6 grid gap-4 md:grid-cols-[minmax(0,1fr)_220px_auto]">
    <div><label class="form-label" for="case-search">Cari kasus</label><div class="input-with-icon"><x-icon name="search" /><input id="case-search" class="form-control" name="q" value="{{ request('q') }}" placeholder="Ketik sebagian nama, NIS, atau NISN..."></div><p class="field-help">Hasil diperbarui otomatis saat Anda mengetik.</p></div>
    <div><label class="form-label" for="case-status">Status</label><select id="case-status" class="form-select" name="status"><option value="">Semua status</option>@foreach(\App\Enums\CaseStatus::cases() as $status)<option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>@endforeach</select></div>
    <button class="btn btn-primary self-end"><x-icon name="search" /> Terapkan</button>
</form>
<div class="card overflow-hidden"><div class="table-wrap"><table class="table"><thead><tr><th>Nomor/tanggal</th><th>Siswa</th><th>Pelanggaran</th><th>Poin</th><th>Status</th><th><span class="sr-only">Aksi</span></th></tr></thead><tbody>@forelse($cases as $case)<tr><td><strong>{{ $case->case_number }}</strong><p class="mt-1 text-xs text-slate-400">{{ $case->occurred_at->translatedFormat('d M Y H:i') }}</p></td><td><strong>{{ $case->student->name }}</strong><p class="mt-1 text-xs text-slate-400">{{ $case->student->currentEnrollment?->schoolClass?->name }}</p></td><td><p class="max-w-sm line-clamp-2 leading-6">{{ $case->items->pluck('instrument_name')->join(', ') }}</p></td><td><span class="badge badge-amber">{{ $case->items->sum('points') }} poin</span></td><td><span class="badge badge-slate">{{ $case->status->label() }}</span></td><td class="text-right"><a class="btn btn-secondary" href="{{ route('cases.show', $case) }}">Buka <x-icon name="arrow-right" /></a></td></tr>@empty<tr><td colspan="6"><div class="empty-state"><x-icon name="clipboard" class="mx-auto mb-3 size-8" /><p>Belum ada kasus.</p></div></td></tr>@endforelse</tbody></table></div></div>
<div class="mt-5">{{ $cases->links() }}</div>
@endsection
