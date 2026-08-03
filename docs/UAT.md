# Checklist UAT Sistem Informasi BK SMAN 1 Dompu

Dokumen ini dipakai bersama guru BK sebelum data nyata dikonfirmasi dan akun siswa diaktifkan. Gunakan data contoh/anonymized pada putaran pertama.

## 1. Persiapan

- Tetapkan satu tahun pelajaran aktif dan buat sedikitnya dua kelas.
- Siapkan akun uji untuk super admin, koordinator BK, guru BK kelas binaan, guru BK kelas lain, dan dua siswa.
- Siapkan satu workbook anonim yang memuat NISN/NIS lengkap, NISN kosong, nama kembar, ejaan berbeda, dan baris ringkasan.
- Siapkan contoh JPG/PNG/WebP dan PDF berukuran di bawah 10 MB.
- Sepakati nama, jabatan, NIP, kop sekolah, serta tata letak tanda tangan yang akan dipakai pada dokumen.

## 2. Hak akses dan akun

| Skenario | Hasil yang diharapkan | Status |
|---|---|---|
| Super admin membuka semua menu | Akun, CMS, audit, impor, instrumen, dan seluruh kasus tersedia | ☐ |
| Koordinator membuka master data | Dapat mengatur kelas/binaan dan mencetak daftar akses siswa, tetapi tidak membuat/mengubah akun staf | ☐ |
| Guru BK membuka siswa di luar binaan | Riwayat dapat dilihat, tetapi identitas/kasus tidak dapat diubah kecuali kasus dibuat sendiri | ☐ |
| Siswa masuk | Cukup menggunakan NISN, NIS, atau kode sementara kelas X tanpa PIN | ☐ |
| Siswa mencoba URL siswa lain/lampiran | Akses ditolak | ☐ |
| Akun staf dinonaktifkan | Tidak dapat masuk kembali | ☐ |

## 3. Impor dan kualitas data

| Skenario | Hasil yang diharapkan | Status |
|---|---|---|
| Unggah workbook yang sama dua kali | Batch lama digunakan kembali; siswa tidak digandakan | ☐ |
| Siswa tanpa NISN/NIS | Mendapat ID sementara dan riwayat tetap dapat dipakai | ☐ |
| NISN diisi pada impor berikutnya | Siswa lama diperbarui melalui ID sistem tanpa memutus riwayat | ☐ |
| Nama mirip/kembar | Masuk review; tidak digabung otomatis | ☐ |
| Sheet gabungan/referensi berbeda | Selisih tampil sebagai konflik | ☐ |
| Commit baris aman | Hanya baris `siap`/`update` yang masuk | ☐ |

Target review workbook asli sebelum commit produksi:

- Kelas X: 433 siswa pada sheet kelas; mayoritas memakai ID sementara karena NISN belum tersedia.
- Kelas XI: 349 siswa; Dafa, Rizciah, dan Husnul harus diperiksa bersama sekolah.
- Kelas XII: 482 siswa; perbedaan ejaan/selisih dari `Sheet1` harus diselesaikan manual.

## 4. Pelanggaran, skor, dan tindak lanjut

| Skenario | Hasil yang diharapkan | Status |
|---|---|---|
| Satu kasus berisi beberapa instrumen | Semua item tersimpan dan poin dijumlahkan | ☐ |
| Instrumen diedit setelah kasus dibuat | Nama, poin, dan sanksi kasus lama tidak berubah | ☐ |
| Kasus dibatalkan | Alasan wajib, poin tidak ikut total, dan perubahan tercatat pada audit | ☐ |
| Total melewati tiap rentang | Tingkat dan persentase sesuai konfigurasi tahun aktif | ☐ |
| Tindak lanjut/pemanggilan dibuat | Agenda tampil pada dashboard dan timeline kasus | ☐ |
| Guru BK menyimpan bersamaan | Nomor kasus tetap unik dan berurutan | ☐ |

## 5. Arsip, PDF, dan home visit

- Cetak rekap pelanggaran, surat pernyataan, panggilan orang tua, berita acara, skorsing, dan home visit.
- Periksa kertas A4, perpindahan tabel panjang, margin, kop, nomor surat, tanggal, serta ruang tanda tangan.
- Pastikan home visit memuat identitas konseli, masalah, tujuan, pelaksanaan, pihak yang ditemui, hasil, tindak lanjut, Guru BK, wali kelas, dan koordinator BK.
- Pastikan kasus tidak dapat dikaitkan dengan dokumen milik siswa lain.
- Pastikan bukti tersimpan privat, tautan berakhir, foto tidak membawa EXIF/GPS, dan portal siswa tidak menampilkan arsip internal.

## 6. CMS, operasi, dan pemulihan

- Edit profil, tata tertib, tata krama, informasi BK, logo, dan identitas sekolah; pastikan HTML berbahaya dibersihkan.
- Jalankan queue worker, scheduler, dan health check `/up` pada VPS.
- Jalankan backup manual, unduh arsip dari S3-compatible, lalu pulihkan database dan private storage pada VPS kosong.
- Catat waktu backup terakhir di dashboard dan pastikan retensi 7 harian, 4 mingguan, dan 6 bulanan.

## 7. Persetujuan aktivasi

Produksi baru boleh diaktifkan setelah seluruh temuan prioritas tinggi ditutup, hasil impor ditandatangani penanggung jawab data, contoh PDF disetujui koordinator BK, pemulihan backup berhasil, dan kode sementara siswa kelas X dibagikan melalui wali kelas atau Guru BK.

| Peran | Nama/tanda tangan | Tanggal |
|---|---|---|
| Koordinator BK |  |  |
| Perwakilan Guru BK |  |  |
| Admin sistem |  |  |
