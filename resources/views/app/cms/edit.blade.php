@extends('layouts.app')
@section('title', 'Edit '.$page->title)
@section('content')
<header class="page-header"><div><a class="back-link" href="{{ route('cms.index') }}"><x-icon name="arrow-left" /> Konten website</a><p class="page-eyebrow"><x-icon name="globe" class="size-4" /> Editor halaman</p><h1 class="page-title">Edit {{ $page->title }}</h1><p class="page-description">Perubahan akan tampil pada website publik setelah halaman diterbitkan.</p></div></header>
<form method="post" action="{{ route('cms.update', $page) }}" class="card mx-auto max-w-5xl overflow-hidden">
    @csrf @method('put')
    <div class="card-header"><div><h2 class="section-title">Isi halaman</h2><p class="section-description">Gunakan struktur konten yang ringkas dan mudah dibaca.</p></div><span class="badge {{ $page->is_published ? 'badge-emerald' : 'badge-slate' }}">{{ $page->is_published ? 'Terbit' : 'Draft' }}</span></div>
    <div class="card-body space-y-5">
        <div><label class="form-label" for="page-title">Judul</label><input id="page-title" class="form-control" name="title" value="{{ old('title', $page->title) }}" required></div>
        <div><label class="form-label" for="page-content">Isi halaman</label><textarea id="page-content" class="form-control font-mono" name="content" rows="20" required>{{ old('content', $page->content) }}</textarea><p class="field-help">Tag yang diizinkan: paragraf, judul, daftar, tebal, miring, dan kutipan.</p></div>
        <label class="flex cursor-pointer items-center gap-2.5 text-sm font-semibold text-slate-700"><input class="size-4 rounded border-slate-300" type="checkbox" name="is_published" value="1" @checked($page->is_published)> Terbitkan halaman</label>
    </div>
    <div class="flex justify-end border-t border-slate-100 bg-slate-50/70 p-5 sm:p-6"><button class="btn btn-primary"><x-icon name="check" /> Simpan perubahan</button></div>
</form>
@endsection
