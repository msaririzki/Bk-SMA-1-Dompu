<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    @include('pdf.partials.style')
</head>
<body>
    @include('pdf.partials.header')
    <div class="title">Rekap Pelanggaran Siswa</div>
    <table class="meta">
        <tr><td class="label">Nama siswa</td><td>: {{ $student->name }}</td></tr>
        <tr><td>NIS / NISN</td><td>: {{ $student->nis ?: '-' }} / {{ $student->nisn ?: '-' }}</td></tr>
        <tr><td>Kelas</td><td>: {{ $student->currentEnrollment?->schoolClass?->name ?? '-' }}</td></tr>
        <tr><td>Tahun pelajaran</td><td>: {{ $year->name }}</td></tr>
    </table>
    <table class="data">
        <thead><tr><th>No</th><th>Tanggal</th><th>Jenis pelanggaran</th><th>Poin</th><th>Status</th></tr></thead>
        <tbody>
            @php($total = 0)
            @forelse ($student->cases as $i => $case)
                @foreach ($case->items as $j => $item)
                    @php($total += $item->points)
                    <tr>
                        <td>{{ $j === 0 ? $i + 1 : '' }}</td>
                        <td>{{ $j === 0 ? $case->occurred_at->format('d-m-Y') : '' }}</td>
                        <td>{{ $item->instrument_name }}</td>
                        <td class="right">{{ $item->points }}</td>
                        <td>{{ $j === 0 ? $case->status->label() : '' }}</td>
                    </tr>
                @endforeach
            @empty
                <tr><td colspan="5" style="text-align: center">Tidak ada catatan pelanggaran.</td></tr>
            @endforelse
        </tbody>
        <tfoot><tr><th colspan="3" class="right">TOTAL POIN</th><th class="right">{{ $total }}</th><th></th></tr></tfoot>
    </table>
    <table class="signature">
        <tr>
            <td><span class="signature-label">Mengetahui,</span><span class="signature-label">Orang Tua/Wali</span><div class="space"></div><span class="signature-label">(........................................)</span></td>
            <td><span class="signature-label">Dompu, {{ now()->translatedFormat('d F Y') }}</span><span class="signature-label">Guru BK</span><div class="space"></div><span class="signature-label">(........................................)</span><span class="signature-label">NIP. ........................</span></td>
        </tr>
    </table>
</body>
</html>
