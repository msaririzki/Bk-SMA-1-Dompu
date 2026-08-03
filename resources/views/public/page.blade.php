@extends('layouts.public')
@section('title',$page->title)
@section('content')
<section class="bg-gradient-to-br from-navy-950 to-navy-800 py-16 text-white"><div class="container-page"><p class="text-sm font-semibold uppercase tracking-widest text-teal-300">SMAN 1 Dompu</p><h1 class="mt-3 text-4xl font-bold text-white">{{ $page->title }}</h1></div></section>
<section class="container-page py-12"><article class="card card-body prose-school mx-auto max-w-4xl">{!! $page->content !!}<p class="mt-10 border-t border-slate-200 pt-5 text-xs text-slate-400">Terakhir diperbarui {{ $page->updated_at->translatedFormat('d F Y') }}</p></article></section>
@endsection
