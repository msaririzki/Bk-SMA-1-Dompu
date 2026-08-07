@extends('layouts.public')

@section('title', 'Beranda')

@section('content')
    <section class="relative overflow-hidden bg-navy-950 text-white">
        <div class="hero-grid pointer-events-none absolute inset-0"></div>
        <div class="pointer-events-none absolute -left-24 top-32 size-80 rounded-full bg-cyan-500/10 blur-3xl"></div>
        <div class="pointer-events-none absolute -right-20 -top-20 size-[28rem] rounded-full bg-teal-400/10 blur-3xl"></div>
        <div class="container-page relative grid items-center gap-14 py-20 lg:grid-cols-[1.05fr_.95fr] lg:py-28">
            <div>
                <span class="inline-flex items-center gap-2 rounded-full border border-teal-300/15 bg-teal-300/10 px-3 py-1.5 text-xs font-bold text-teal-200"><span class="size-1.5 rounded-full bg-teal-300"></span>Layanan BK Terpadu</span>
                <h1 class="mt-7 max-w-3xl text-4xl font-extrabold leading-[1.08] tracking-[-.045em] text-white sm:text-6xl">Sistem informasi BK untuk pendampingan siswa SMAN 1 Dompu.</h1>
                <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-300">Pencatatan pelanggaran, pembinaan, tindak lanjut, dan arsip administrasi kini tersimpan dalam satu ruang kerja yang aman.</p>
                <div class="mt-9 flex flex-wrap gap-3"><a class="btn btn-accent" href="{{ route('login') }}"><x-icon name="lock" />Masuk sebagai guru BK</a><a class="btn btn-student" href="{{ route('student.login') }}"><x-icon name="user" />Cek pelanggaran siswa</a></div>
                <div class="mt-10 flex flex-wrap gap-x-7 gap-y-3 text-xs font-semibold text-slate-400"><span class="flex items-center gap-2"><x-icon name="check" class="size-4 text-teal-400" />Arsip privat</span><span class="flex items-center gap-2"><x-icon name="check" class="size-4 text-teal-400" />Riwayat terstruktur</span><span class="flex items-center gap-2"><x-icon name="check" class="size-4 text-teal-400" />Siap cetak PDF</span></div>
            </div>

            <div class="relative mx-auto w-full max-w-xl sm:mb-24 lg:mx-0 lg:mb-20">
                <div class="glass-card relative overflow-hidden p-4 sm:p-5">
                    <div class="flex items-center justify-between border-b border-white/[.08] pb-4"><div class="flex items-center gap-3"><span class="grid size-9 place-items-center rounded-xl bg-teal-400 font-black text-navy-950">BK</span><div><p class="text-sm font-bold text-white">Pratinjau dashboard</p><p class="text-[11px] text-slate-400">Ringkasan data saat ini</p></div></div><span class="flex items-center gap-2 rounded-full bg-emerald-400/10 px-2.5 py-1 text-[10px] font-bold text-emerald-300"><span class="size-1.5 rounded-full bg-emerald-300"></span>Aman</span></div>
                    <div class="mt-4 grid grid-cols-3 gap-3">
                        @foreach ([['Siswa', $preview['students']], ['Kasus bulan ini', $preview['month_cases']], ['Perlu tindak lanjut', $preview['open_cases']]] as [$label, $value])
                            <div class="rounded-2xl border border-white/[.07] bg-white/[.05] p-3"><p class="text-[10px] font-semibold text-slate-400">{{ $label }}</p><p class="mt-1.5 text-xl font-extrabold text-white">{{ number_format($value, 0, ',', '.') }}</p></div>
                        @endforeach
                    </div>
                    <div class="mt-3 rounded-2xl border border-white/[.07] bg-white/[.05] p-4"><div class="flex items-center justify-between"><div><p class="text-xs font-bold text-white">Prioritas penanganan</p><p class="mt-1 text-[10px] text-slate-400">Persentase siswa berdasarkan poin tahunan</p></div><x-icon name="chart" class="size-5 text-teal-300" /></div><div class="mt-5 space-y-4">@foreach ($preview['priorities'] as $priority)<div><div class="mb-1.5 flex justify-between text-[10px] font-semibold text-slate-300"><span>{{ $priority['label'] }} <span class="text-slate-500">({{ $priority['count'] }} siswa)</span></span><span>{{ $priority['percentage'] }}%</span></div><div class="h-1.5 rounded-full bg-white/[.07]"><div class="h-1.5 rounded-full {{ $priority['color'] }}" style="width: {{ $priority['percentage'] }}%"></div></div></div>@endforeach</div></div>
                </div>
                <div class="absolute -bottom-20 -left-5 hidden rounded-2xl border border-white/10 bg-navy-900/90 p-4 shadow-2xl backdrop-blur sm:block"><div class="flex items-center gap-3"><span class="grid size-10 place-items-center rounded-xl bg-teal-400/10 text-teal-300"><x-icon name="shield" /></span><div><p class="text-xs font-bold text-white">Data terlindungi</p><p class="mt-0.5 text-[10px] text-slate-400">Sesuai peran pengguna</p></div></div></div>
            </div>
        </div>
    </section>

    <section class="container-page py-20">
        <div class="mx-auto max-w-2xl text-center"><p class="page-eyebrow justify-center">Informasi sekolah</p><h2 class="text-3xl font-extrabold sm:text-4xl">Pedoman dan layanan dalam satu tempat</h2><p class="mt-4 leading-7 text-slate-500">Akses informasi penting bagi siswa, orang tua, dan seluruh warga sekolah dengan tampilan yang mudah dipahami.</p></div>
        <div class="mt-10 grid gap-5 md:grid-cols-3">
            @foreach ([
                ['route' => 'public.rules', 'icon' => 'clipboard', 'badge' => 'Pedoman', 'badgeClass' => 'badge-emerald', 'title' => 'Tata tertib murid', 'description' => 'Ketentuan kehadiran, seragam, larangan, hak, kewajiban, dan sanksi.'],
                ['route' => 'public.etiquette', 'icon' => 'students', 'badge' => 'Karakter', 'badgeClass' => 'badge-amber', 'title' => 'Tata krama', 'description' => 'Rambu-rambu bersikap, berucap, dan bertindak di lingkungan sekolah.'],
                ['route' => 'public.bk', 'icon' => 'shield', 'badge' => 'Layanan', 'badgeClass' => 'badge-slate', 'title' => 'Informasi BK', 'description' => 'Pendampingan pribadi, sosial, akademik, karier, dan penanganan siswa.'],
            ] as $item)
                <a href="{{ route($item['route']) }}" class="card card-body group relative overflow-hidden"><div class="feature-icon"><x-icon :name="$item['icon']" /></div><span class="badge {{ $item['badgeClass'] }} mt-6">{{ $item['badge'] }}</span><h3 class="mt-4 text-xl font-extrabold transition group-hover:text-teal-700">{{ $item['title'] }}</h3><p class="mt-2 text-sm leading-6 text-slate-500">{{ $item['description'] }}</p><span class="mt-6 inline-flex items-center gap-2 text-sm font-bold text-teal-700">Baca selengkapnya <x-icon name="arrow-right" class="size-4 transition group-hover:translate-x-1" /></span></a>
            @endforeach
        </div>
    </section>

    <section class="container-page pb-4">
        <div class="relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-navy-900 to-navy-800 px-6 py-10 text-white shadow-2xl shadow-navy-900/15 sm:px-10 lg:flex lg:items-center lg:justify-between lg:px-12"><div class="pointer-events-none absolute right-0 top-0 size-64 rounded-full bg-teal-400/10 blur-3xl"></div><div class="relative"><p class="text-xs font-bold uppercase tracking-[.16em] text-teal-300">Portal pribadi siswa</p><h2 class="mt-3 max-w-2xl text-2xl font-extrabold text-white sm:text-3xl">Lihat riwayat dan progres pelanggaran siswa secara mudah.</h2><p class="mt-3 max-w-xl text-sm leading-6 text-slate-300">Masuk menggunakan NISN, NIS, atau kode sementara kelas X yang diberikan sekolah.</p></div><a class="btn btn-student relative mt-7 shrink-0 lg:mt-0" href="{{ route('student.login') }}">Cek pelanggaran siswa <x-icon name="arrow-right" /></a></div>
    </section>
@endsection
