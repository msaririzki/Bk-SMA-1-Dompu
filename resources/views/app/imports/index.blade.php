@extends('layouts.app')
@section('title', 'Impor Siswa')
@section('content')
<header class="page-header">
    <div><p class="page-eyebrow"><x-icon name="upload" class="size-4" /> Data onboarding</p><h1 class="page-title">Impor data siswa</h1><p class="page-description">Setiap data masuk ke tahap validasi dan review sebelum resmi tersimpan.</p></div>
    <a class="btn btn-secondary" href="{{ route('imports.template') }}"><x-icon name="documents" /> Unduh template standar</a>
</header>
<section class="card overflow-hidden">
    <div class="card-header"><div class="flex items-center gap-3"><span class="feature-icon"><x-icon name="upload" /></span><div><h2 class="section-title">Unggah workbook Excel</h2><p class="section-description">Pilih tahun pelajaran dan berkas sumber.</p></div></div><span class="badge badge-emerald">Aman direview</span></div>
    <form method="post" enctype="multipart/form-data" action="{{ route('imports.store') }}" class="card-body grid gap-4 md:grid-cols-[240px_minmax(0,1fr)_auto]">
        @csrf
        <div><label class="form-label" for="academic_year_id">Tahun pelajaran</label><select id="academic_year_id" class="form-select" name="academic_year_id" required>@foreach($years as $year)<option value="{{ $year->id }}">{{ $year->name }}{{ $year->is_active ? ' — aktif' : '' }}</option>@endforeach</select></div>
        <div><label class="form-label" for="import-file">Berkas .xlsx</label><input id="import-file" class="form-control" type="file" name="file" accept=".xlsx" required></div>
        <button class="btn btn-primary self-end"><x-icon name="upload" /> Unggah & analisis</button>
    </form>
    <div class="border-t border-slate-100 bg-slate-50/80 px-5 py-4 text-xs leading-5 text-slate-500 sm:px-6"><strong class="text-slate-700">Catatan:</strong> Sheet kelas menjadi sumber impor. GABUNG, DATA AWAL, dan Sheet1 hanya dipakai untuk menemukan selisih/ejaan yang perlu direview; URUT PEMINATAN tidak diimpor.</div>
</section>
<section class="card mt-6 overflow-hidden">
    <div class="card-header"><div><h2 class="section-title">Riwayat impor</h2><p class="section-description">Lacak hasil analisis dan konflik setiap berkas.</p></div></div>
    <div class="table-wrap"><table class="table"><thead><tr><th>File</th><th>Status</th><th>Baris</th><th>Konflik</th><th>Waktu</th><th><span class="sr-only">Aksi</span></th></tr></thead><tbody>@forelse($batches as $batch)<tr><td><div class="flex items-center gap-3"><span class="grid size-9 place-items-center rounded-xl bg-emerald-50 text-emerald-700"><x-icon name="documents" class="size-4" /></span><strong class="max-w-xs truncate">{{ $batch->original_name }}</strong></div></td><td><span class="badge badge-slate">{{ $batch->status }}</span></td><td>{{ $batch->total_rows }}</td><td><span class="font-bold {{ $batch->conflict_rows ? 'text-orange-600' : 'text-slate-700' }}">{{ $batch->conflict_rows }}</span></td><td>{{ $batch->created_at->translatedFormat('d M Y H:i') }}</td><td class="text-right"><a class="btn btn-secondary" href="{{ route('imports.show', $batch) }}">Review <x-icon name="arrow-right" /></a></td></tr>@empty<tr><td colspan="6"><div class="empty-state"><x-icon name="upload" class="mx-auto mb-3 size-8" /><p>Belum ada impor.</p></div></td></tr>@endforelse</tbody></table></div>
</section>
<div class="mt-5">{{ $batches->links() }}</div>
@endsection
