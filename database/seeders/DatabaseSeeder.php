<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\AcademicYear;
use App\Models\CmsPage;
use App\Models\SchoolSetting;
use App\Models\SeverityLevel;
use App\Models\User;
use App\Models\ViolationCategory;
use App\Models\ViolationInstrument;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(['username' => env('INITIAL_ADMIN_USERNAME', 'admin')], [
            'name' => env('INITIAL_ADMIN_NAME', 'Administrator BK'),
            'email' => null,
            'role' => UserRole::SuperAdmin,
            'password' => Hash::make(env('INITIAL_ADMIN_PASSWORD') ?: 'Admin123!'),
            'must_change_password' => true,
            'is_active' => true,
        ]);

        AcademicYear::updateOrCreate(['name' => '2026/2027'], [
            'starts_at' => '2026-07-01', 'ends_at' => '2027-06-30', 'is_active' => true,
        ]);

        $levels = [
            ['Ringan', 1, 24, 'emerald', 'Pemantauan dan pembinaan'],
            ['Sedang', 25, 49, 'amber', 'Peringatan dan pembinaan terarah'],
            ['Berat', 50, 74, 'orange', 'Pemanggilan orang tua'],
            ['Sangat Berat', 75, 94, 'rose', 'Penanganan segera'],
            ['Kritis', 95, 99, 'red', 'Surat peringatan akhir'],
            ['Ambang Tindakan', 100, null, 'slate', 'Keputusan sekolah'],
        ];
        foreach ($levels as $i => [$name,$min,$max,$color,$action]) {
            SeverityLevel::updateOrCreate(['name' => $name], ['min_points' => $min, 'max_points' => $max, 'color' => $color, 'recommended_action' => $action, 'sort_order' => $i + 1]);
        }

        $categories = [
            'A' => 'Keterlambatan', 'B' => 'Kehadiran', 'C' => 'Pakaian Seragam', 'D' => 'Kepribadian',
            'E' => 'Ketertiban', 'F' => 'Merokok', 'G' => 'Pornografi', 'H' => 'Senjata Tajam',
            'I' => 'Narkoba dan Minuman Keras', 'J' => 'Berkelahi / Tawuran', 'K' => 'Intimidasi / Ancaman dengan Kekerasan',
        ];
        foreach ($categories as $i => $name) {
            ViolationCategory::updateOrCreate(['code' => $i], ['name' => $name, 'sort_order' => ord($i)]);
        }

        $rules = [
            ['A', 'A01', 'Terlambat masuk sekolah', 5, 'Teguran dan kegiatan pembinaan/kebersihan.'],
            ['A', 'A02', 'Keluar sekolah saat istirahat tanpa izin', 10, 'Dimintai keterangan dan diberikan surat pembinaan.'],
            ['B', 'B01', 'Tidak masuk tanpa keterangan selama 3 kali', 25, 'Surat perjanjian dan pemanggilan orang tua.'],
            ['B', 'B02', 'Izin tidak melalui guru atau wali kelas', 10, 'Dimintai keterangan dan surat pembinaan.'],
            ['B', 'B03', 'Meninggalkan KBM tanpa izin', 10, 'Dimintai keterangan dan surat pembinaan.'],
            ['B', 'B04', 'Tidak mengikuti ekstrakurikuler yang dipilih', 10, 'Surat pernyataan dan pembinaan.'],
            ['B', 'B05', 'Tidak mengikuti upacara bendera', 10, 'Pembinaan dan tugas kebersihan.'],
            ['C', 'C01', 'Seragam tidak rapi atau sepatu tidak sesuai ketentuan', 10, 'Teguran, perapian, dan penyitaan sesuai berita acara.'],
            ['C', 'C02', 'Pakaian/rok/celana ketat atau tidak sesuai ukuran', 15, 'Pembinaan, surat pernyataan, dan pemanggilan orang tua bila berulang.'],
            ['C', 'C03', 'Celana ketat, model pensil, berkantung banyak, atau menggantung', 15, 'Pembinaan dan pemanggilan orang tua.'],
            ['C', 'C04', 'Seragam tidak sesuai jadwal', 15, 'Dipulangkan untuk mengganti pakaian dengan surat keterangan.'],
            ['C', 'C05', 'Memakai atribut yang tidak ditentukan sekolah', 10, 'Atribut disita dan dibuat berita acara.'],
            ['C', 'C06', 'Menggunakan HP di kelas tanpa izin guru', 10, 'Penyitaan sementara dan pemanggilan orang tua jika berulang.'],
            ['C', 'C07', 'Membuat atribut dengan nama sekolah tanpa izin', 15, 'Pembinaan, surat pernyataan, dan pemanggilan orang tua bila berulang.'],
            ['C', 'C08', 'Memakai kaos kaki tidak sesuai ketentuan', 5, 'Pembinaan dan penyitaan kaos kaki.'],
            ['D', 'D01', 'Bersolek berlebihan di lingkungan sekolah', 5, 'Peralatan diamankan dan dilakukan pembinaan.'],
            ['D', 'D02', 'Memakai perhiasan berlebihan', 5, 'Pembinaan moral.'],
            ['D', 'D03', 'Memakai aksesori gelang, kalung, atau cincin', 5, 'Barang diamankan.'],
            ['D', 'D04', 'Memiliki tindik atau tato', 100, 'Dikembalikan kepada orang tua.'],
            ['D', 'D05', 'Rambut putra gondrong atau model tidak sesuai', 15, 'Peringatan dan pencukuran bila tidak diperbaiki.'],
            ['D', 'D06', 'Memelihara kuku panjang', 5, 'Kuku dipotong dan diberikan pembinaan.'],
            ['D', 'D07', 'Mewarnai rambut selain hitam', 10, 'Warna rambut dikembalikan ke warna asli.'],
            ['D', 'D08', 'Rambut perempuan tergerai/tidak diikat', 5, 'Pembinaan dan diminta mengikat rambut.'],
            ['D', 'D09', 'Mengeluarkan kata-kata tidak sopan', 10, 'Pembinaan moral dan permohonan maaf.'],
            ['D', 'D10', 'Mencemarkan nama baik sekolah di media sosial', 50, 'Pembinaan dan pemanggilan orang tua.'],
            ['D', 'D11', 'Menghina, memperolok, memfitnah, atau melakukan bullying', 50, 'Pembinaan dan pemanggilan orang tua.'],
            ['D', 'D12', 'Melakukan perjudian dan sejenisnya', 100, 'Dikembalikan kepada orang tua.'],
            ['D', 'D13', 'Mengancam siswa secara verbal/nonverbal atau melalui media sosial', 50, 'Teguran, surat pernyataan, dan pemanggilan orang tua.'],
            ['D', 'D14', 'Berbohong atau membuat kesaksian palsu', 50, 'Teguran, surat pernyataan, dan pemanggilan orang tua.'],
            ['D', 'D15', 'Melakukan perbuatan asusila', 100, 'Dikembalikan kepada orang tua.'],
            ['D', 'D16', 'Melakukan pembunuhan', 100, 'Dikembalikan kepada orang tua dan diserahkan kepada kepolisian.'],
            ['D', 'D17', 'Melakukan pemalakan', 75, 'Teguran, surat pernyataan, dan pemanggilan orang tua.'],
            ['D', 'D18', 'Melakukan pencurian', 100, 'Dikembalikan kepada orang tua.'],
            ['D', 'D19', 'Menikah atau hamil/menghamili selama menjadi siswa', 100, 'Dikembalikan kepada orang tua.'],
            ['D', 'D20', 'Berkumpul di luar sekolah memakai seragam saat KBM', 20, 'Pembinaan pada hari berikutnya.'],
            ['D', 'D21', 'Melawan guru atau pegawai sekolah', 75, 'Pembinaan, pemanggilan orang tua, dan surat perjanjian.'],
            ['E', 'E01', 'Merusak sarana dan prasarana sekolah', 50, 'Mengganti kerusakan, surat perjanjian, dan pemanggilan orang tua.'],
            ['E', 'E02', 'Membuat kegaduhan di dalam/luar sekolah', 10, 'Peringatan.'],
            ['E', 'E03', 'Melompat pagar sekolah', 25, 'Peringatan dan sanksi kebersihan.'],
            ['E', 'E04', 'Tidak melaksanakan piket kelas', 10, 'Melaksanakan tugas kebersihan dengan pengawasan.'],
            ['E', 'E05', 'Tidur atau makan di kelas saat KBM tanpa izin', 5, 'Teguran dan membersihkan ruangan.'],
            ['E', 'E06', 'Membuang permen karet sembarangan', 15, 'Membersihkan bekas permen karet.'],
            ['E', 'E07', 'Mengajak orang luar masuk tanpa izin', 20, 'Dimintai keterangan dan surat teguran.'],
            ['E', 'E08', 'Meludah atau membuang sampah sembarangan', 10, 'Pembinaan moral dan sanksi kebersihan.'],
            ['F', 'F01', 'Membawa atau menghisap rokok/vape', 50, 'Barang disita, pembinaan, dan pemanggilan orang tua.'],
            ['G', 'G01', 'Membawa, menyimpan, atau mengakses konten pornografi', 50, 'Surat peringatan, pemanggilan orang tua, dan surat pernyataan.'],
            ['G', 'G02', 'Membuat atau mengirim konten pornografi', 50, 'Surat peringatan, pemanggilan orang tua, dan surat pernyataan.'],
            ['G', 'G03', 'Mengunggah konten pornografi ke media sosial', 50, 'Surat peringatan, pemanggilan orang tua, dan surat pernyataan.'],
            ['H', 'H01', 'Membawa senjata tajam/api ke sekolah', 100, 'Dikembalikan kepada orang tua dan diserahkan kepada kepolisian.'],
            ['H', 'H02', 'Memperjualbelikan senjata tajam/api', 100, 'Dikembalikan kepada orang tua dan diserahkan kepada kepolisian.'],
            ['H', 'H03', 'Menggunakan senjata untuk melukai orang lain', 100, 'Dikembalikan kepada orang tua dan diserahkan kepada kepolisian.'],
            ['I', 'I01', 'Mabuk di lingkungan sekolah akibat narkoba/miras', 100, 'Dikembalikan kepada orang tua dan diserahkan kepada kepolisian.'],
            ['I', 'I02', 'Membawa, mengonsumsi, atau memperjualbelikan narkoba/miras', 100, 'Dikembalikan kepada orang tua dan diserahkan kepada kepolisian.'],
            ['J', 'J01', 'Berkelahi/tawuran dengan siswa sekolah lain', 100, 'Dikembalikan kepada orang tua dan diserahkan kepada kepolisian.'],
            ['J', 'J02', 'Berkelahi antarsiswa/kelas', 100, 'Dikembalikan kepada orang tua.'],
            ['J', 'J03', 'Menjadi provokator perkelahian', 100, 'Dikembalikan kepada orang tua.'],
            ['K', 'K01', 'Mengancam atau mengintimidasi warga sekolah', 100, 'Dikembalikan kepada orang tua.'],
            ['K', 'K02', 'Menganiaya, menghina, atau mencemarkan nama baik warga sekolah', 100, 'Dikembalikan kepada orang tua.'],
            ['K', 'K03', 'Berurusan dengan pihak berwajib karena tindak kriminal', 100, 'Dikembalikan kepada orang tua.'],
        ];
        foreach ($rules as $i => [$category,$code,$name,$points,$sanction]) {
            ViolationInstrument::updateOrCreate(['code' => $code], ['category_id' => ViolationCategory::where('code', $category)->value('id'), 'name' => $name, 'points' => $points, 'sanction' => $sanction, 'is_active' => true, 'sort_order' => $i + 1]);
        }

        $pages = [
            ['profil', 'Profil SMAN 1 Dompu', '<p>SMAN 1 Dompu berkomitmen membentuk murid yang berkarakter, cerdas, dan berbudaya melalui lingkungan belajar yang aman serta kolaboratif.</p>'],
            ['tata-tertib', 'Tata Tertib Murid', '<h2>Waktu belajar dan kehadiran</h2><p>Murid wajib hadir paling lambat 15 menit sebelum pukul 07.00 WITA, mengikuti literasi dan imtaq, serta melapor kepada guru BP/BK dan guru piket bila terlambat.</p><h2>Pakaian dan kerapian</h2><p>Seragam harus bersih, rapi, lengkap, dan sesuai jadwal sekolah.</p><h2>Larangan</h2><p>Dilarang membawa barang berbahaya, rokok, miras, zat adiktif, HP tanpa izin, melakukan perundungan, perkelahian, perjudian, atau meninggalkan sekolah tanpa izin.</p>'],
            ['tata-krama', 'Tata Krama Murid', '<p>Tata krama menjadi rambu-rambu bagi murid dalam bersikap, berucap, dan bertindak berdasarkan nilai sekolah dan masyarakat sekitar.</p><p>Murid wajib menghormati warga sekolah, menjaga kebersihan, merawat sarana prasarana, dan mengikuti kegiatan pembiasaan serta ibadah berjamaah.</p>'],
            ['informasi-bk', 'Informasi Bimbingan dan Konseling', '<p>Layanan BK membantu siswa berkembang secara pribadi, sosial, akademik, dan karier. Setiap penanganan dicatat secara aman dan ditindaklanjuti bersama pihak terkait.</p>'],
        ];
        foreach ($pages as [$slug,$title,$content]) {
            CmsPage::updateOrCreate(['slug' => $slug], ['title' => $title, 'content' => $content, 'is_published' => true]);
        }

        foreach (['school_name' => 'SMAN 1 Dompu', 'tagline' => 'Berkarakter, Cerdas dan Berbudaya', 'school_address' => 'Dompu, Nusa Tenggara Barat', 'school_phone' => '', 'coordinator_name' => 'Koordinator BK', 'coordinator_nip' => ''] as $key => $value) {
            SchoolSetting::updateOrCreate(['key' => $key], ['value' => $value, 'type' => 'text']);
        }
    }
}
