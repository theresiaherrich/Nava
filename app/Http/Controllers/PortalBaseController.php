<?php

namespace App\Http\Controllers;

use Carbon\Carbon;

/**
 * Dasar untuk semua controller A4 - Portal Orang Tua.
 *
 * Berisi helper tampilan dan data bersama (siswa, orang tua, nilai, presensi, dst).
 * Semua data masih DUMMY: method "data*" adalah titik sambung ke database.
 * Ganti isinya dengan query Eloquent, tapi pertahankan bentuk hasilnya agar Blade tidak berubah.
 */
abstract class PortalBaseController extends Controller
{
    protected const KKTP          = 75;
    protected const NILAI_OPTIMAL = 85;
    protected const HARI_EFEKTIF  = 80;

    /* ======================================================================
     |  Helper tampilan
     * ====================================================================== */

    protected function sapaan(): string
    {
        $jam = (int) now()->format('G');

        return match (true) {
            $jam < 11 => 'Selamat Pagi',
            $jam < 15 => 'Selamat Siang',
            $jam < 18 => 'Selamat Sore',
            default   => 'Selamat Malam',
        };
    }

    /** Konversi nilai -> predikat. Sesuaikan dengan aturan sekolah. */
    protected function predikat(float $n): string
    {
        return match (true) {
            $n >= 90 => 'A',
            $n >= 85 => 'B+',
            $n >= 80 => 'B',
            $n >= self::KKTP => 'C',
            default  => 'D',
        };
    }

    /** Konversi nilai -> ketercapaian TP. */
    protected function ketercapaian(float $n): array
    {
        return match (true) {
            $n >= 90 => ['label' => 'Sangat Baik', 'kelas' => 'sangat-baik'],
            $n >= 80 => ['label' => 'Baik', 'kelas' => 'baik'],
            $n >= self::KKTP => ['label' => 'Cukup', 'kelas' => 'cukup'],
            default  => ['label' => 'Perlu Penguatan', 'kelas' => 'perlu'],
        };
    }

    protected function rataRata($nilai): float
    {
        return round((float) $nilai->avg('nilai'), 2);
    }

    protected function labelStatus(): array
    {
        return [
            'hadir'     => 'Hadir Tepat Waktu',
            'toleransi' => 'Terlambat Toleransi',
            'izin'      => 'Izin',
            'sakit'     => 'Sakit',
            'alpha'     => 'Alpha',
            'libur'     => 'Libur',
            'belum'     => 'Belum berlangsung',
        ];
    }

    /**
     * Log RFID satu hari. DUMMY: status dibuat deterministik dari tanggalnya.
     * Ganti dengan query tabel presensi/log RFID.
     */
    protected function logHarian(Carbon $d, Carbon $hariIni): array
    {
        $dasar = [
            'tanggal_label' => $d->copy()->locale('id')->translatedFormat('l, j F Y'),
            'status' => 'belum', 'masuk' => null, 'pulang' => null, 'keterangan' => null,
            'gerbang' => null, 'suhu' => null, 'metode' => null, 'catatan' => null,
        ];

        if ($d->isWeekend()) {
            return ['status' => 'libur', 'keterangan' => 'Libur akhir pekan'] + $dasar;
        }
        if ($d->gt($hariIni)) {
            return $dasar;
        }

        $hari = $d->day;

        if ($hari === 8) {
            return ['status' => 'sakit', 'keterangan' => 'Surat dokter terverifikasi',
                    'catatan' => 'Siswa tidak hadir karena sakit (kondisi medis).'] + $dasar;
        }
        if ($hari === 15) {
            return ['status' => 'izin', 'keterangan' => 'Dispensasi OSN Kebumian',
                    'catatan' => 'Mengikuti kegiatan lomba mewakili sekolah.'] + $dasar;
        }

        $toleransi = $hari % 9 === 3 && $hari > 3;
        $menit     = $toleransi ? 56 + ($hari % 8) : 30 + (($hari * 7) % 24);   // gerbang ditutup 06.55
        $masuk     = Carbon::createFromTime(6, 0)->addMinutes($menit);

        return [
            'status'     => $toleransi ? 'toleransi' : 'hadir',
            'masuk'      => $masuk->format('H.i'),
            'pulang'     => '15.' . str_pad((string) (30 + ($hari % 10)), 2, '0', STR_PAD_LEFT),
            'keterangan' => $toleransi ? 'Masuk di jam toleransi' : 'Hadir tepat waktu',
            'gerbang'    => 'Gerbang Utama Tugu',
            'suhu'       => '36.' . ($hari % 6) . '°C (Normal)',
            'metode'     => 'Smart Card ID Siswa',
            'catatan'    => 'KBM normal & praktikum berjalan sesuai jadwal.',
        ] + $dasar;
    }

    /* ======================================================================
     |  DATA DUMMY  ->  ganti dengan query database
     * ====================================================================== */

    protected function dataSiswa(): object
    {
        $lahir = Carbon::create(2009, 5, 14);

        return (object) [
            'nama'          => 'Raditya Arya Pratama',
            'nama_pendek'   => 'Raditya Arya',
            'inisial'       => 'RA',
            'nis'           => '222310452',
            'nisn'          => '0078945612',
            'no_presensi'   => 24,
            'kelas'         => 'XI - MIPA 1',
            'kelas_singkat' => 'XI-MIPA 1',
            'peminatan'     => 'Peminatan Unggulan Sains Terpadu',
            'fase'          => 'Fase F',
            'tahun_ajaran'  => '2026/2027',
            'semester'      => 'Ganjil',
            'angkatan'      => 2025,
            'jalur'         => 'Jalur Prestasi Akademik',
            'status'        => 'Siswa Reguler Aktif',
            'foto'          => null,                                    // mis. 'images/siswa/222310452.jpg' (di /public)
            'tempat_lahir'  => 'Malang',
            'tanggal_lahir' => $lahir,
            'umur'          => $lahir->age,
            'jenis_kelamin' => 'Laki-laki',
            'agama'         => 'Islam',
            'alamat'        => 'Jl. Ijen No. 42, RT 03 / RW 02, Kel. Gading Kasri, Kec. Klojen, Kota Malang, Jawa Timur 65115',
            'jarak_sekolah' => '± 1.4 km',
            'kurikulum'     => 'Kurikulum Merdeka Mandiri Berbagi',
            'ruang'         => 'Gedung Mitreka Satata Lt. 2, R.204',
            'rombel_label'  => 'Kelas XI - MIPA 1 (Sains Unggulan)',
            'wali_kelas'    => (object) [
                'nama'    => 'Dra. Endang Kusuma, M.Pd.',
                'nip'     => '19740512 199802 2 001',
                'inisial' => 'EK',
                'tersedia' => true,
            ],
        ];
    }

    /** Akun orang tua yang sedang login. */
    protected function dataOrtu(): object
    {
        return (object) [
            'nama'    => 'Ibu Sri Rahayu',
            'peran'   => 'Wali Raditya Arya (XI-MIPA 1)',
            'foto'    => null,
            'inisial' => 'SR',
        ];
    }

    protected function dataWaliMurid(): array
    {
        return [
            (object) ['nama' => 'Sri Rahayu, S.E.', 'hubungan' => 'Ibu Kandung', 'inisial' => 'SR',
                      'keterangan' => 'Terhubung sebagai Penanggung Jawab Utama', 'utama' => true,
                      'wa' => '+62 812-3456-7890'],
            (object) ['nama' => 'Hendra Pratama, M.T.', 'hubungan' => 'Ayah Kandung', 'inisial' => 'HP',
                      'keterangan' => 'Terdaftar dalam Berkas Kesiswaan SMAN 1', 'utama' => false,
                      'wa' => null],
        ];
    }

    protected function dataKegiatan(): array
    {
        return [
            (object) ['judul' => 'Kepramukaan (Wajib)', 'ikon' => 'compass', 'badge' => 'Nilai: A (Sangat Baik)', 'tipe' => 'hijau',
                      'detail' => 'Ambalan Mitreka Satata Gugus Depan SMAN 1 Kota Malang'],
            (object) ['judul' => 'Klub Robotika & IT (Mitreka IT)', 'ikon' => 'file', 'badge' => 'Anggota Aktif', 'tipe' => 'biru',
                      'detail' => 'Divisi Pemrograman Algoritma & Olimpiade Sains Komputer'],
            (object) ['judul' => 'Juara 2 OSN-K Informatika 2026', 'ikon' => 'trophy', 'badge' => 'Tingkat Kota', 'tipe' => 'emas',
                      'detail' => 'Olimpiade Sains Nasional jenjang SMA se-Kota Malang • Lolos Seleksi Provinsi'],
        ];
    }

    protected function dataKehadiranHariIni(): object
    {
        return (object) ['jam' => '06.42', 'gerbang' => 'Gate Utara', 'label' => 'Hadir Tepat Waktu'];
    }

    protected function dataPresensiSemester(): object
    {
        $hadir = 78; $sakit = 1; $izin = 1; $alpha = 0;
        $efektif = self::HARI_EFEKTIF;

        return (object) [
            'efektif'      => $efektif,
            'hadir'        => $hadir,
            'sakit'        => $sakit,
            'izin'         => $izin,
            'alpha'        => $alpha,
            'tepat_waktu'  => 75,
            'toleransi'    => 3,
            'persen_hadir' => round($hadir / $efektif * 100, 1),
            'persen_sakit' => round($sakit / $efektif * 100, 2),
            'persen_izin'  => round($izin / $efektif * 100, 2),
            'persen_alpha' => round($alpha / $efektif * 100, 2),
            'tren'         => 1.2,
            'ket_sakit'    => 'Surat dokter terverifikasi',
            'ket_izin'     => 'Dispensasi OSN Kebumian',
        ];
    }

    /** @return \Illuminate\Support\Collection<int, object> */
    protected function dataNilai()
    {
        $rows = [
            // [mapel, kelompok, nilai, guru, capaian]
            ['Pendidikan Agama & Budi Pekerti', 'Umum', 90, 'Drs. Ahmad Fauzi, M.Pd.I', 'Memahami toleransi beragama dan implementasi akhlak mulia dalam pergaulan sekolah.'],
            ['Pendidikan Pancasila', 'Umum', 88, 'Siti Aminah, S.Pd', 'Aktif dalam penalaran kritis norma hukum konstitusional dan tata kelola kewarganegaraan.'],
            ['Bahasa Indonesia', 'Umum', 87, 'Dra. Lestari Wulandari', 'Mahir menyusun teks eksposisi dan artikel ilmiah populer dengan rujukan literatur valid.'],
            ['Matematika', 'Umum', 86, 'Bpk. Agus Hendrawan, M.Si', 'Menerapkan konsep fungsi dan statistika dalam pemecahan masalah kontekstual.'],
            ['Bahasa Inggris', 'Umum', 90, 'Ms. Dian Anggraini, S.Pd', 'Menyampaikan gagasan secara lisan dan tulisan dengan tata bahasa yang tepat.'],
            ['Sejarah Indonesia', 'Umum', 86, 'Drs. Bambang Irawan', 'Menganalisis peristiwa sejarah secara kronologis dan kausal.'],
            ['PJOK', 'Umum', 89, 'Rudi Hartono, S.Pd', 'Menunjukkan kebugaran jasmani dan sportivitas yang baik dalam permainan.'],
            ['Informatika', 'Umum', 93, 'Yoga Prasetyo, S.Kom', 'Menyusun algoritma dan program sederhana secara efisien dan terstruktur.'],
            ['Seni Budaya', 'Umum', 87, 'Rina Maharani, S.Sn', 'Mengapresiasi dan mengekspresikan karya seni dengan kreatif.'],
            ['Muatan Lokal Bahasa Jawa', 'Umum', 86, 'Sri Wahyuni, S.Pd', 'Menggunakan unggah-ungguh basa dalam komunikasi sehari-hari.'],
            ['Matematika Tingkat Lanjut', 'Peminatan MIPA', 94, 'Bpk. Agus Hendrawan, M.Si', 'Sangat menguasai konsep matriks, polinomial, dan trigonometri analitis berstandar olimpiade.'],
            ['Fisika', 'Peminatan MIPA', 91, 'Ibu Ratna Dewi, S.Pd', 'Menunjukkan pemahaman mendalam dinamika fluida, asas Bernoulli, dan gelombang bunyi.'],
            ['Kimia', 'Peminatan MIPA', 89, 'Drs. M. Taufik, M.Pd', 'Menguasai stoikiometri larutan, konfigurasi orbital, dan peramalan ikatan kimia molekuler.'],
            ['Biologi', 'Peminatan MIPA', 85, 'Dewi Kartika, S.Si', 'Memahami bioteknologi konvensional dan mekanisme metabolisme sel tumbuhan secara deskriptif.'],
            ['Bahasa Inggris Tingkat Lanjut', 'Pilihan', 92, 'Ms. Dian Anggraini, S.Pd', 'Fasih dalam presentasi akademik, debat terstruktur, dan penulisan esai kritis bertema iptek.'],
        ];

        return collect($rows)->map(function ($r, $i) {
            return (object) [
                'no'          => $i + 1,
                'mapel'       => $r[0],
                'kelompok'    => $r[1],
                'kelompok_kelas' => $r[1] === 'Umum' ? 'umum' : ($r[1] === 'Pilihan' ? 'pilihan' : 'mipa'),
                'nilai'       => $r[2],
                'guru'        => $r[3],
                'capaian'     => $r[4],
                'predikat'    => $this->predikat($r[2]),
                'tp'          => $this->ketercapaian($r[2]),
                'kktp'        => self::KKTP,
            ];
        });
    }

    protected function dataCatatanSekolah(): array
    {
        return [
            (object) [
                'nama' => 'Dra. Endang Kusuma, M.Pd.', 'peran' => 'Wali Kelas XI MIPA 1',
                'nip' => '19740512 199802 2 001', 'tanggal' => Carbon::create(2026, 10, 28),
                'isi' => 'Raditya menunjukkan dedikasi belajar yang luar biasa dan aktif berdiskusi di kelas. Mohon tetap didampingi dalam manajemen waktu antara tugas akademik reguler dan jadwal pemusatan latihan lomba OSN tingkat provinsi agar waktu istirahat tetap terjaga.',
                'inisial' => 'EK', 'aksi' => 'Balas Catatan', 'aksi_ikon' => 'message',
            ],
            (object) [
                'nama' => 'Drs. Heru Suwondo', 'peran' => 'Guru Bimbingan Konseling (BK)',
                'nip' => '19710408 199802 1 003', 'tanggal' => Carbon::create(2026, 11, 2),
                'isi' => 'Potensi logika analitis Raditya sangat matang dan konsisten. Direkomendasikan mempertahankan ritme belajar ini menjelang persiapan pemilihan program studi perguruan tinggi (Fakultas Teknik / Ilmu Komputer) pada awal Semester Genap nanti.',
                'inisial' => 'HS', 'aksi' => 'Jadwalkan Konsultasi BK', 'aksi_ikon' => 'clock',
            ],
        ];
    }

    protected function dataPortofolio(): array
    {
        return [
            (object) ['judul' => 'Praktikum Fisika & Analisis Fluida', 'foto' => 'images/portal/praktikum-fisika.jpg',
                      'teks' => 'Aktif menyelesaikan 8 modul berbasis proyek saintifik dengan predikat kepemimpinan kelompok memuaskan.'],
            (object) ['judul' => 'Presentasi Kolokium Matematika', 'foto' => 'images/portal/kolokium-matematika.jpg',
                      'teks' => 'Berpartisipasi aktif dalam sesi tanya jawab kritis seputar aplikasi aljabar linear pada komputasi modern.'],
        ];
    }

    /** Skor RIASEC 0-100 dari hasil tes minat. */
    protected function dataRiasec(): object
    {
        $nama = [
            'R' => 'Realistik', 'I' => 'Investigatif', 'A' => 'Artistik',
            'S' => 'Sosial', 'E' => 'Enterprising', 'C' => 'Konvensional',
        ];
        $skor = ['R' => 58, 'I' => 92, 'A' => 46, 'S' => 52, 'E' => 61, 'C' => 84];

        arsort($skor);
        $dua = array_slice(array_keys($skor), 0, 2);

        return (object) [
            'skor'      => $skor,
            'nama'      => $nama,
            'kode'      => implode('', $dua),
            'judul'     => $nama[$dua[0]] . ' & ' . $nama[$dua[1]],
            'diperbarui' => Carbon::create(2026, 10, 14),
            'oleh'      => 'Guru BK',
            'tag'       => ['RIASEC Fase F', 'Teknik & Sains'],
        ];
    }

    protected function dataTes(): object
    {
        return (object) [
            'status'   => 'Selesai & Valid',
            'tanggal'  => Carbon::create(2026, 10, 14),
            'penyelenggara' => 'Layanan Bimbingan Konseling SMAN 1 Malang',
        ];
    }

    protected function dataBk(): object
    {
        return (object) [
            'nama'    => 'Drs. Heru Suwondo',
            'status'  => 'Direkomendasikan',
            'tanggal' => Carbon::create(2026, 11, 2),
            'catatan' => 'Potensi logika analitis sangat matang dan konsisten. Dianjurkan mempertahankan ritme belajar ini dan mulai menyusun peta pilihan perguruan tinggi pada semester genap.',
        ];
    }

    protected function dataRekomendasiProdi(): array
    {
        return [
            (object) ['rumpun' => 'Teknik & Informatika', 'cocok' => 94,
                      'prodi' => ['Teknik Informatika (ITS / UB)', 'Sains Data', 'Teknik Elektro'],
                      'alasan' => 'Skor Investigatif dan Konvensional tertinggi, selaras dengan capaian Matematika Tingkat Lanjut dan prestasi OSN Informatika.'],
            (object) ['rumpun' => 'Sains Murni & Matematika', 'cocok' => 88,
                      'prodi' => ['Matematika', 'Statistika', 'Fisika'], 'alasan' => null],
            (object) ['rumpun' => 'Teknik Industri & Sistem', 'cocok' => 81,
                      'prodi' => ['Teknik Industri', 'Sistem Informasi'], 'alasan' => null],
        ];
    }

    protected function dataLangkahKarier(): array
    {
        return [
            (object) ['judul' => 'Seleksi OSN tingkat Provinsi', 'waktu' => 'Semester Ganjil', 'status' => 'berjalan'],
            (object) ['judul' => 'Konsultasi pemilihan program studi dengan Guru BK', 'waktu' => 'Awal Semester Genap', 'status' => 'berikut'],
            (object) ['judul' => 'Sosialisasi SNBP & pemilihan konsentrasi perguruan tinggi', 'waktu' => 'Semester Genap', 'status' => 'berikut'],
            (object) ['judul' => 'Persiapan portofolio prestasi untuk jalur prestasi', 'waktu' => 'Kelas XII', 'status' => 'berikut'],
        ];
    }

    protected function dataPengumuman(): array
    {
        return [
            (object) ['kategori' => 'Kurikulum & Asesmen', 'tanggal' => Carbon::create(2026, 11, 18), 'ikon' => 'calendar',
                      'judul' => 'Jadwal Penilaian Akhir Semester (SAS) Ganjil 2026/2027',
                      'ringkas' => 'Pelaksanaan Sumatif Akhir Semester dimulai serentak pada pekan ketiga November.'],
            (object) ['kategori' => 'Paguyuban SMAN 1', 'tanggal' => Carbon::create(2026, 11, 12), 'ikon' => 'users',
                      'judul' => 'Undangan Pertemuan Paguyuban Orang Tua Siswa Kelas XI',
                      'ringkas' => 'Pertemuan koordinasi program pembinaan bakat dan prestasi siswa.'],
            (object) ['kategori' => 'Bimbingan Karier', 'tanggal' => Carbon::create(2026, 11, 5), 'ikon' => 'cap',
                      'judul' => 'Sosialisasi SNBP & Pemilihan Konsentrasi Perguruan Tinggi',
                      'ringkas' => 'Panduan jalur prestasi nasional dan perumusan strategi pemilihan program studi.'],
        ];
    }
}
