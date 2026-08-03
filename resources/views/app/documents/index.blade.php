@extends('layouts.app')
@section('title', 'Dokumen dan Home Visit')
@section('content')
<header class="page-header">
    <div><p class="page-eyebrow"><x-icon name="documents" class="size-4" /> Arsip digital</p><h1 class="page-title">Dokumen dan home visit</h1><p class="page-description">Surat, laporan, dan bukti tindak lanjut terhubung langsung ke map digital siswa.</p></div>
    <div class="flex flex-wrap gap-2"><a class="btn btn-secondary" href="{{ route('home-visits.create') }}"><x-icon name="home" /> Home visit</a><a class="btn btn-primary" href="{{ route('documents.create') }}"><x-icon name="plus" /> Buat dokumen</a></div>
</header>
<div class="card overflow-hidden"><div class="table-wrap"><table class="table"><thead><tr><th>Tanggal/nomor</th><th>Siswa</th><th>Jenis</th><th>Status</th><th><span class="sr-only">Aksi</span></th></tr></thead><tbody>@forelse($documents as $document)<tr><td><strong>{{ $document->document_date->translatedFormat('d M Y') }}</strong><p class="mt-1 text-xs text-slate-400">{{ $document->number ?: 'Tanpa nomor' }}</p></td><td><div class="flex items-center gap-3"><span class="student-avatar size-9 text-xs">{{ mb_strtoupper(mb_substr($document->student->name, 0, 1)) }}</span><strong>{{ $document->student->name }}</strong></div></td><td><span class="font-semibold text-slate-700">{{ $document->type->label() }}</span></td><td><span class="badge badge-emerald">{{ $document->status }}</span></td><td class="text-right"><a class="btn btn-secondary" href="{{ route('documents.show', $document) }}">Buka <x-icon name="arrow-right" /></a></td></tr>@empty<tr><td colspan="5"><div class="empty-state"><x-icon name="documents" class="mx-auto mb-3 size-8" /><p>Belum ada dokumen.</p><p class="mt-1 text-xs">Dokumen baru akan muncul di sini setelah dibuat.</p></div></td></tr>@endforelse</tbody></table></div></div>
<div class="mt-5">{{ $documents->links() }}</div>
@endsection
