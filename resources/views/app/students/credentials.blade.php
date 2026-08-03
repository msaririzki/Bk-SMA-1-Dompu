<!doctype html>
<html lang="id">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Akun siswa {{ $class->name }}</title>@vite(['resources/css/app.css'])</head>
<body class="p-4 sm:p-8">
    <div class="mx-auto max-w-5xl">
        <div class="no-print mb-7 flex flex-wrap items-center justify-between gap-3"><a class="btn btn-secondary" href="{{ route('students.index') }}"><x-icon name="arrow-left" /> Kembali</a><button class="btn btn-primary" onclick="window.print()"><x-icon name="documents" /> Cetak daftar</button></div>
        <header class="mb-6 border-b-2 border-navy-900 pb-5"><p class="page-eyebrow">Administrasi akun</p><h1 class="text-2xl font-extrabold text-navy-950">Akses Portal Siswa — {{ $class->name }}</h1><p class="mt-2 text-sm text-slate-500">Siswa masuk cukup menggunakan NISN, NIS, atau kode sementara yang tercantum pada daftar ini.</p></header>
        <div class="card overflow-hidden"><div class="table-wrap"><table class="table"><thead><tr><th>No</th><th>Nama</th><th>Identitas masuk</th></tr></thead><tbody>@forelse($credentials as $i => $row)<tr><td>{{ $i + 1 }}</td><td><strong>{{ $row['name'] }}</strong></td><td class="font-mono font-bold">{{ $row['username'] }}</td></tr>@empty<tr><td colspan="3"><div class="empty-state">Semua siswa di kelas ini sudah memiliki akun.</div></td></tr>@endforelse</tbody></table></div></div>
    </div>
</body>
</html>
