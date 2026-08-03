# Sistem Informasi BK SMAN 1 Dompu

Aplikasi administrasi Bimbingan dan Konseling untuk pencatatan pelanggaran siswa, perhitungan prioritas, tindak lanjut, home visit, dokumen PDF, impor Excel, portal siswa, CMS publik, audit, dan backup terenkripsi.

## Fitur utama

- Empat peran: super admin, koordinator BK, guru BK, dan siswa.
- Identitas siswa permanen berbasis UUID; NIS dan NISN dapat kosong serta selalu disimpan sebagai string.
- Riwayat kelas per tahun pelajaran melalui enrollment, sehingga kenaikan kelas tidak menimpa data lama.
- Importer Excel bertahap dengan staging, hash file, normalisasi, deteksi konflik, review, dan commit idempoten.
- Instrumen pelanggaran dapat diedit/nonaktifkan; kasus lama tetap memakai snapshot nama, poin, dan sanksi.
- Skor tahunan dan seluruh masa sekolah, tingkat prioritas, persentase menuju ambang tindakan, serta dashboard.
- Surat pernyataan, panggilan orang tua, berita acara, skorsing, home visit, dan rekap PDF A4.
- Bukti foto/PDF privat, signed URL 10 menit, pembatasan otorisasi, re-encoding gambar tanpa EXIF/GPS, dan thumbnail privat.
- Portal siswa hanya menampilkan ringkasan miliknya tanpa foto, surat, home visit, atau catatan internal.
- Audit perubahan sensitif dan backup ZIP AES-256 ke S3-compatible dengan retensi 7 harian, 4 mingguan, dan 6 bulanan.

## Menjalankan secara lokal

Persyaratan: PHP 8.3+ (target produksi PHP 8.5), Composer 2, Node.js 24, ekstensi GD/ZIP/Intl/PDO, dan MySQL 8. Untuk mencoba tanpa MySQL, SQLite juga dapat digunakan.

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Isi konfigurasi database pada `.env`, lalu:

```bash
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

Akun awal memakai username `admin`. Kata sandi diambil dari `INITIAL_ADMIN_PASSWORD`; bila kosong pada lingkungan lokal, seeder memakai `Admin123!`. Pengguna wajib mengganti kata sandi saat pertama masuk. Jangan gunakan kata sandi bawaan di produksi.

## Alur impor data siswa

Masuk sebagai super admin/koordinator, buka **Impor siswa**, pilih tahun pelajaran, lalu unggah satu workbook `.xlsx`. Sistem membaca sheet kelas, mengabaikan sheet gabungan/ringkasan, dan menampilkan setiap baris sebagai:

- `siap`: identitas baru yang aman dibuat;
- `update`: cocok melalui UUID, NISN, atau NIS;
- `konflik`: kemiripan nama atau identitas tidak konsisten dan harus ditinjau;
- `invalid`: kolom wajib tidak valid.

Nama tidak pernah digunakan untuk merge otomatis. Tekan **Commit baris aman** hanya setelah ringkasan dan konflik diperiksa. Workbook asli dan hasil unggahan disimpan privat serta dilarang masuk Git.

## Backup dan pemulihan

Isi `BACKUP_PASSWORD` dengan rahasia kuat serta kredensial S3-compatible pada `.env`. Scheduler menjalankan:

```bash
php artisan bk:backup
```

setiap pukul 01.30 WITA. Uji manual dapat dijalankan dengan `php artisan bk:backup --disk=local --keep-local`. Backup mencakup dump database serta seluruh arsip privat. Untuk pemulihan, unduh arsip, buka dengan kata sandi backup, pulihkan SQL menggunakan `mysql`, salin folder `private` ke `storage/app/private`, lalu jalankan `php artisan migrate --force`. Simpan kata sandi backup di password manager terpisah dari VPS.

## Deployment VPS dengan Docker

1. Pasang Docker Engine, Docker Compose, Nginx host, dan Certbot.
2. Salin `.env.example` menjadi `.env`; set `APP_ENV=production`, `APP_DEBUG=false`, URL HTTPS, database, kata sandi admin awal, `DB_ROOT_PASSWORD`, serta S3/backup.
3. Ubah domain pada `deploy/vps-host-nginx.conf`, terbitkan sertifikat Certbot, dan aktifkan file tersebut pada Nginx host.
4. Jalankan `docker compose up -d --build`.
5. Untuk rilis berikutnya jalankan `bash deploy/deploy.sh`.

Compose menjalankan PHP-FPM, Nginx internal pada `127.0.0.1:8080`, MySQL 8.4, queue worker, dan scheduler. Endpoint health check tersedia di `/up`. Verifikasi pemulihan backup pada VPS kosong sebelum data nyata diaktifkan.

## Pemeriksaan sebelum produksi

```bash
php artisan test
npm run build
php artisan route:list --except-vendor
php artisan schedule:list
```

Lakukan UAT bersama guru BK dengan data contoh. Setelah alur surat, tanda tangan, kelas binaan, serta kategori skor disetujui, baru commit hasil impor data asli dan buat akun siswa per kelas.
