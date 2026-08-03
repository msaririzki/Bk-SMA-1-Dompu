<!doctype html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>@yield('title') — Sistem BK</title>@vite(['resources/css/app.css','resources/js/app.js'])</head>
<body class="min-h-screen bg-navy-950">
<div class="grid min-h-screen lg:grid-cols-2">
    <div class="hidden items-end bg-gradient-to-br from-navy-900 via-navy-800 to-teal-700 p-12 text-white lg:flex"><div class="max-w-lg"><span class="badge bg-white/10 text-teal-100">SMAN 1 Dompu</span><h1 class="mt-5 text-5xl font-bold text-white">Administrasi BK yang rapi, aman, dan mudah ditindaklanjuti.</h1><p class="mt-5 text-lg text-slate-200">Pelanggaran, pembinaan, dokumen, dan home visit tersimpan dalam satu map digital.</p></div></div>
    <div class="flex items-center justify-center bg-slate-50 p-6"><div class="w-full max-w-md">@yield('content')<a href="{{ route('home') }}" class="mt-6 block text-center text-sm text-slate-500 hover:text-teal-700">← Kembali ke beranda</a></div></div>
</div></body></html>
