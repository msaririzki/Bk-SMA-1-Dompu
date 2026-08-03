<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    @include('pdf.partials.style')
</head>
<body class="home-visit">
    @include('pdf.partials.header')
    <div class="title">Laporan Kunjungan Rumah<br><span style="font-size: 9pt; font-style: italic">(Home Visit)</span></div>
    @php($visit = $document->homeVisit)

    <div class="keep-together">
        <h3>A. IDENTITAS KONSELI</h3>
        <table class="meta">
            <tr><td class="label">1. Nama Konseli/Siswa</td><td>: {{ $visit->counselee_name }}</td></tr>
            <tr><td>2. Kelas</td><td>: {{ $visit->class_name }}</td></tr>
            <tr><td>3. Jenis Kelamin</td><td>: {{ $visit->gender }}</td></tr>
            <tr><td>4. Alamat</td><td>: {{ $visit->address ?: '-' }}</td></tr>
            <tr><td>5. Nama Orang Tua/Wali</td><td>: {{ $visit->parent_name ?: '-' }}</td></tr>
        </table>
    </div>
    <div class="keep-together"><h3>B. PERMASALAHAN KONSELI</h3><p class="content">{{ $visit->problem }}</p></div>
    <div class="keep-together"><h3>C. TUJUAN HOME VISIT</h3><p class="content">{{ $visit->purpose }}</p></div>
    <div class="keep-together">
        <h3>D. PELAKSANAAN KUNJUNGAN RUMAH</h3>
        <table class="meta">
            <tr><td class="label">1. Tanggal Pelaksanaan</td><td>: {{ $visit->visit_date->translatedFormat('d F Y') }}</td></tr>
            <tr><td>2. Yang ditemui</td><td>: {{ $visit->met_with ?: '-' }}</td></tr>
        </table>
    </div>
    <div class="keep-together"><h3>E. HASIL HOME VISIT</h3><p class="content">{{ $visit->result }}</p></div>
    <div class="keep-together"><h3>F. TINDAK LANJUT</h3><p class="content">{{ $visit->follow_up }}</p></div>

    <div class="signature-block">
        <table class="sign-three">
            <tr>
                <td><span class="signature-label">Guru BK</span><div class="space"></div><strong class="signature-label">{{ $visit->counselor_name }}</strong><span class="signature-label">NIP. {{ $visit->counselor_nip ?: '........................' }}</span></td>
                <td><span class="signature-label">{{ $visit->place }}, {{ $visit->visit_date->translatedFormat('d F Y') }}</span><span class="signature-label">Wali Kelas</span><div class="space"></div><strong class="signature-label">{{ $visit->homeroom_name }}</strong><span class="signature-label">NIP. {{ $visit->homeroom_nip ?: '........................' }}</span></td>
            </tr>
        </table>
        <div class="center-sign"><span class="signature-label">Mengetahui,</span><span class="signature-label">Koordinator Bimbingan dan Konseling (BK)</span><div class="space"></div><strong class="signature-label">{{ $visit->coordinator_name }}</strong><span class="signature-label">NIP. {{ $visit->coordinator_nip ?: '........................' }}</span></div>
        <div class="bottom-title">LAPORAN KUNJUNGAN RUMAH</div>
    </div>
</body>
</html>
