@extends('layouts.public')
@section('title', $page->title)
@section('content')
<section class="public-page-hero">
    <div class="public-grid"></div>
    <div class="container-page relative py-14 sm:py-20">
        <nav class="mb-7 flex items-center gap-2 text-sm text-white/60"><a href="{{ route('home') }}" class="transition hover:text-white">Beranda</a><span>/</span><span class="text-teal-200">{{ $page->title }}</span></nav>
        <p class="page-eyebrow text-teal-300">Informasi sekolah</p>
        <h1 class="mt-3 max-w-3xl text-4xl font-extrabold tracking-tight text-white sm:text-5xl">{{ $page->title }}</h1>
        <p class="mt-4 max-w-2xl text-sm leading-7 text-slate-300 sm:text-base">Informasi resmi dan panduan bagi seluruh warga SMAN 1 Dompu.</p>
    </div>
</section>
<section class="container-page py-10 sm:py-14">
    <article class="card mx-auto max-w-5xl overflow-hidden">
        <div class="h-1.5 bg-gradient-to-r from-teal-500 via-cyan-400 to-sky-500"></div>
        <div class="card-body prose-school px-6 py-8 sm:px-10 sm:py-10">
            {!! $page->content !!}
            <div class="mt-10 flex items-center gap-2 border-t border-slate-200 pt-5 text-xs text-slate-400"><x-icon name="calendar" class="size-4" /> Terakhir diperbarui {{ $page->updated_at->translatedFormat('d F Y') }}</div>
        </div>
    </article>
</section>
@endsection
