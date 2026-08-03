@extends('layouts.app')
@section('title', 'Catat Pelanggaran')
@section('content')
<header class="page-header">
    <div>
        <a class="back-link" href="{{ route('cases.index') }}"><x-icon name="arrow-left" /> Daftar kasus</a>
        <p class="page-eyebrow"><x-icon name="clipboard" class="size-4" /> Formulir kejadian</p>
        <h1 class="page-title">Catat pelanggaran siswa</h1>
        <p class="page-description">Poin langsung berlaku setelah disimpan. Pastikan siswa, kronologi, dan instrumen telah dipilih dengan teliti.</p>
    </div>
</header>

<form method="post" action="{{ route('cases.store') }}" enctype="multipart/form-data" class="grid items-start gap-6 xl:grid-cols-[minmax(0,.82fr)_minmax(0,1.18fr)]">
    @csrf
    <div class="space-y-6">
        <section class="card">
            <div class="card-header"><div class="flex items-center gap-3"><span class="feature-icon size-10"><x-icon name="calendar" class="size-5" /></span><div><h2 class="section-title">Detail kejadian</h2><p class="text-xs text-slate-400">Informasi faktual saat kejadian.</p></div></div><span class="badge badge-amber">Wajib diisi</span></div>
            <div class="card-body space-y-5">
                <x-student-autocomplete :student="$selectedStudent" />
                <div class="grid gap-4 sm:grid-cols-2"><div><label class="form-label" for="occurred_at">Tanggal dan waktu</label><input id="occurred_at" class="form-control" type="datetime-local" name="occurred_at" value="{{ old('occurred_at', now()->format('Y-m-d\TH:i')) }}" required></div><div><label class="form-label" for="location">Lokasi</label><input id="location" class="form-control" name="location" value="{{ old('location') }}" placeholder="Contoh: Kelas X-2"></div></div>
                <div><label class="form-label" for="chronology">Kronologi</label><textarea id="chronology" class="form-control" name="chronology" rows="7" required placeholder="Tuliskan fakta kejadian secara objektif...">{{ old('chronology') }}</textarea><p class="field-help">Gunakan bahasa objektif dan hindari asumsi pribadi.</p></div>
                <div><label class="form-label" for="attachments">Bukti foto/dokumen</label><input id="attachments" class="form-control" type="file" name="attachments[]" multiple accept="image/jpeg,image/png,image/webp,application/pdf"><p class="field-help"><x-icon name="documents" class="mt-0.5 size-3.5 shrink-0" /> Maksimal 10 berkas, masing-masing 10 MB. Format JPG, PNG, WEBP, atau PDF.</p></div>
            </div>
        </section>
        <div class="alert alert-warning"><x-icon name="alert" class="size-5 shrink-0" /><p><strong class="block">Periksa sebelum menyimpan</strong><span class="mt-1 block text-xs leading-5 opacity-80">Poin akan langsung masuk ke perhitungan siswa dan setiap perubahan berikutnya tercatat dalam audit.</span></p></div>
    </div>

    <section class="card overflow-hidden xl:sticky xl:top-24">
        <div class="card-header"><div class="flex items-center gap-3"><span class="feature-icon size-10"><x-icon name="scale" class="size-5" /></span><div><h2 class="section-title">Instrumen pelanggaran</h2><p class="text-xs text-slate-400">Satu kejadian dapat memuat beberapa pelanggaran.</p></div></div></div>
        <div class="max-h-[62vh] overflow-y-auto border-b border-slate-100">
            @foreach($categories as $category)
                <div class="border-b border-slate-100 p-5 last:border-b-0 sm:p-6">
                    <div class="flex items-center gap-2"><span class="grid size-7 place-items-center rounded-lg bg-navy-900 text-xs font-extrabold text-white">{{ $category->code }}</span><h3 class="font-extrabold text-navy-900">{{ $category->name }}</h3></div>
                    <div class="mt-4 space-y-2">
                        @foreach($category->instruments as $instrument)
                            <label class="instrument-option">
                                <input class="mt-1 size-4 shrink-0 rounded border-slate-300 accent-teal-600" type="checkbox" name="instrument_ids[]" value="{{ $instrument->id }}" @checked(in_array($instrument->id, old('instrument_ids', [])))>
                                <span class="min-w-0 flex-1"><span class="block text-sm font-bold text-slate-800">{{ $instrument->code }} · {{ $instrument->name }}</span><span class="mt-1 block text-xs leading-5 text-slate-500">{{ $instrument->sanction }}</span></span>
                                <span class="badge badge-amber h-fit shrink-0">{{ $instrument->points }} poin</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
        <div class="flex flex-col-reverse gap-3 bg-slate-50/70 p-5 sm:flex-row sm:items-center sm:justify-end sm:p-6"><a class="btn btn-secondary" href="{{ route('cases.index') }}">Batal</a><button class="btn btn-primary"><x-icon name="check" /> Simpan pelanggaran</button></div>
    </section>
</form>
@endsection
