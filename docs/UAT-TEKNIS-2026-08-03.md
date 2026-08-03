# Hasil UAT Teknis - 3 Agustus 2026

Dokumen ini mencatat pemeriksaan teknis dengan database SQLite anonim yang terpisah dari staging data siswa asli. Hasil ini belum menggantikan persetujuan UAT dari koordinator dan guru BK.

## Cakupan yang lolos

- Seeder UAT dapat dijalankan ulang tanpa menggandakan data: 3 siswa anonim, 2 kelas, 2 kasus, 1 tindak lanjut, 2 dokumen, dan 1 home visit.
- Dashboard menampilkan agregasi poin, tingkat prioritas, agenda, dan distribusi kelas saat data kasus tersedia.
- Guru BK dapat melihat seluruh riwayat, tetapi pilihan siswa dan tombol perubahan hanya tersedia untuk kelas binaan atau kasus yang dibuatnya. URL perubahan di luar kewenangan tetap ditolak oleh server.
- Portal siswa hanya menampilkan tanggal, instrumen, poin, tingkat, persentase, dan status tindak lanjut miliknya. Kronologi internal, kontak orang tua, home visit, dokumen, dan data siswa lain tidak tampil.
- Laporan review konflik impor mempunyai sheet `Ringkasan` dan `Review Konflik`; NIS/NISN dengan nol di depan tetap terlihat, filter aktif, dan tidak terdapat formula rusak.
- Surat pernyataan dan rekap pelanggaran tervalidasi satu halaman A4. Home visit tervalidasi dua halaman A4: isi A-F pada halaman pertama serta area tanda tangan manual dan judul penutup pada halaman kedua.
- Seluruh test aplikasi, formatter, build produksi, Composer audit, dan NPM audit berhasil.

## Temuan yang ditutup

1. Query agregasi poin dashboard gagal pada SQLite ketika kasus sudah ada. Query kini memakai alias agregat yang konsisten lintas database dan memiliki test regresi.
2. Aksi edit/buat kasus dan dokumen masih terlihat bagi guru yang tidak membina kelas terkait. Daftar pilihan dan tombol kini mengikuti policy, sementara pemeriksaan server tetap dipertahankan.
3. Judul tindak lanjut home visit terpisah dari isinya serta tanda tangan rekap bertumpuk. CSS cetak dan struktur blok tanda tangan telah diperbaiki dan dirender ulang.

## Pemeriksaan yang masih memerlukan sekolah

- Selesaikan review 36 konflik staging workbook asli: 10 kelas X, 3 kelas XI, dan 23 kelas XII. Jangan commit baris aman sebelum keputusan sekolah dicatat.
- Konfirmasi kop, logo, nama, jabatan, NIP, redaksi surat, dan pembagian halaman home visit dengan koordinator BK.
- Uji unggahan foto dari HP sekolah dan satu dokumen PDF contoh pada perangkat yang akan dipakai.
- Uji pergantian PIN pertama dan mekanisme pembagian PIN melalui jalur tertutup.
- Lakukan pemulihan backup terenkripsi pada VPS kosong sebelum aktivasi produksi.

Gunakan checklist utama di [`UAT.md`](UAT.md) untuk tanda tangan persetujuan akhir.
