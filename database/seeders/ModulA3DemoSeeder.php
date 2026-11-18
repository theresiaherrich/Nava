<?php

namespace Database\Seeders;

use App\Models\DimensiRiasec;
use App\Models\Guru;
use App\Models\HasilTesRiasec;
use App\Models\JawabanRiasec;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\PenugasanBk;
use App\Models\Profesi;
use App\Models\ProgramStudi;
use App\Models\RekomendasiSepuluh;
use App\Models\RiwayatKelas;
use App\Models\Semester;
use App\Models\SesiTes;
use App\Models\Siswa;
use App\Models\SoalRiasec;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ModulA3DemoSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password123');

        // ==========================================
        // 1. TAHUN AJARAN & SEMESTER AKTIF
        // ==========================================
        $ta = TahunAjaran::firstOrCreate(
            ['nama' => '2026/2027'],
            ['aktif' => 1]
        );

        $semester = Semester::firstOrCreate(
            ['id_tahun_ajaran' => $ta->id_tahun_ajaran, 'nama' => 'GASAL'],
            ['aktif' => 1]
        );

        // ==========================================
        // 2. DIMENSI RIASEC (6 Dimensi)
        // ==========================================
        $dimensiData = [
            ['R', 'Realistic',     'Menyukai pekerjaan praktis, mekanikal, aktivitas fisik, alat, atau alam terbuka.'],
            ['I', 'Investigative', 'Menyukai pemecahan masalah ilmiah, analisis data, penelitian, dan berpikir logis.'],
            ['A', 'Artistic',      'Menyukai kreativitas, berekspresi bebas, seni, desain, bahasa, dan ide orisinal.'],
            ['S', 'Social',        'Menyukai membantu sesama, mengajar, bimbingan, empati, dan berinteraksi sosial.'],
            ['E', 'Enterprising',  'Menyukai kepemimpinan, bernegosiasi, memengaruhi orang lain, dan berwirausaha.'],
            ['C', 'Conventional',  'Menyukai keteraturan, administrasi data, ketelitian, prosedur, dan sistem terstruktur.'],
        ];

        $dimensiMap = [];
        foreach ($dimensiData as [$kode, $nama, $deskripsi]) {
            $d = DimensiRiasec::firstOrCreate(
                ['kode' => $kode],
                ['nama' => $nama, 'deskripsi' => $deskripsi]
            );
            $dimensiMap[$kode] = $d->id_dimensi;
        }

        // ==========================================
        // 3. 12 SOAL RIASEC AKTIF (2 per dimensi - sesuai ATURAN_A3)
        // ==========================================
        $soalList = [
            // Realistic (2 soal)
            ['R', 'Saya suka merakit, memperbaiki alat elektronik, mesin, atau benda di lingkungan sekitar.', 1],
            ['R', 'Saya lebih menikmati kegiatan praktek fisik di laboratorium atau luar ruangan dibanding duduk mendengarkan teori.', 2],
            // Investigative (2 soal)
            ['I', 'Saya gemar menganalisis pola masalah ilmiah dan mencari tahu fakta di balik suatu fenomena.', 3],
            ['I', 'Saya senang memecahkan perhitungan rumit, teka-teki logika, atau melakukan eksperimen mandiri.', 4],
            // Artistic (2 soal)
            ['A', 'Saya suka mendesain karya visual, menulis cerita, atau mengekspresikan ide kreatif tanpa aturan kaku.', 5],
            ['A', 'Saya memiliki apresiasi tinggi terhadap estetika, komposisi warna, seni pertunjukan, atau arsitektur.', 6],
            // Social (2 soal)
            ['S', 'Saya merasa terpanggil saat mendengarkan keluh kesah dan membantu teman mengatasi kesulitan belajarnya.', 7],
            ['S', 'Saya menikmati kerja sama tim, mengajar, dan berdiskusi kelompok untuk memecahkan masalah bersama.', 8],
            // Enterprising (2 soal)
            ['E', 'Saya senang memimpin jalannya rapat kepanitiaan dan meyakinkan orang lain terhadap ide-ide saya.', 9],
            ['E', 'Saya bersemangat melihat peluang bisnis baru dan berani mengambil risiko untuk mencapainya.', 10],
            // Conventional (2 soal)
            ['C', 'Saya selalu menyusun berkas, catatan, atau jadwal kegiatan secara rapi dan sistematis.', 11],
            ['C', 'Saya merasa nyaman mengikuti panduan langkah kerja yang detail dan teliti dalam mengolah data angka.', 12],
        ];

        foreach ($soalList as [$kode, $teks, $urutan]) {
            SoalRiasec::updateOrCreate(
                ['id_dimensi' => $dimensiMap[$kode], 'urutan' => $urutan],
                ['teks' => $teks, 'aktif' => 1]
            );
        }

        // ==========================================
        // 4. MAPEL PILIHAN KELAS X (Tabel mapel)
        // ==========================================
        $mapelPilihan = [
            ['KODE-FIS', 'Fisika Lanjut', 'Sains & Teknologi', 'IRS', 'Mempelajari hukum alam, mekanika lanjutan, dan fenomena energi.'],
            ['KODE-BIO', 'Biologi & Rekayasa Hayati', 'Sains Hayati', 'ISA', 'Mempelajari keanekaragaman hayati, bioteknologi terapan, dan ekologi.'],
            ['KODE-KIM', 'Kimia Terapan', 'Sains Terapan', 'IRE', 'Mempelajari reaksi kimia industri, material modern, dan farmakologi dasar.'],
            ['KODE-INF', 'Informatika & Pemrograman', 'Teknologi Informasi', 'IRC', 'Mempelajari algoritma terapan, rekayasa perangkat lunak, dan kecerdasan buatan.'],
            ['KODE-EKO', 'Ekonomi Terapan', 'Sosial Humaniora', 'ECS', 'Mempelajari dinamika pasar modal, akuntansi manajerial, dan kewirausahaan modern.'],
            ['KODE-SOS', 'Sosiologi & Riset Masyarakat', 'Sosial Humaniora', 'SAE', 'Mempelajari dinamika sosial, metode riset kemasyarakatan, dan kebijakan publik.'],
            ['KODE-GEO', 'Geografi Spasial & SIG', 'Ilmu Bumi & Spasial', 'RIS', 'Mempelajari sistem informasi geografis, pemetaan digital, dan mitigasi bencana.'],
            ['KODE-SEN', 'Desain Komunikasi Visual', 'Seni & Budaya', 'ASE', 'Mempelajari prinsip desain visual, tipografi digital, dan komunikasi kreatif.'],
        ];

        foreach ($mapelPilihan as [$kode, $nama, $rumpun, $riasec, $deskripsi]) {
            $hurufAwal = substr($riasec, 0, 1);
            Mapel::updateOrCreate(
                ['kode' => $kode],
                [
                    'id_dimensi'       => $dimensiMap[$hurufAwal] ?? 1,
                    'nama'             => $nama,
                    'rumpun'           => $rumpun,
                    'deskripsi'        => $deskripsi,
                    'is_pilihan'       => 1,
                    'kode_riasec'      => $riasec,
                    'aktif'            => 1,
                    'status_validasi'  => 'tervalidasi',
                ]
            );
        }

        // ==========================================
        // 5. PROFESI & PROGRAM STUDI KELAS XII
        // ==========================================
        $profesiData = [
            ['Software Engineer', 'Merancang dan mengembangkan arsitektur perangkat lunak skala tinggi.'],
            ['DevOps & Cloud Engineer', 'Mengelola infrastruktur cloud server, automasi CI/CD, dan keandalan sistem 24/7.'],
            ['Cybersecurity Analyst', 'Mengidentifikasi celah keamanan, menguji penetrasi sistem, dan mencegah peretasan.'],
            ['Data Scientist', 'Mengolah big data dan melatih model kecerdasan buatan berbasis machine learning.'],
            ['Data Analyst', 'Menganalisis tren transaksi bisnis dan menyajikan dashboard visual eksekutif.'],
            ['Data Engineer', 'Membangun jalur pipa data otomatis (ETL) berkapasitas pemrosesan terabytes.'],
            ['Dokter Umum', 'Mendiagnosis dan mengobati penyakit serta memberikan layanan medis primer bagi publik.'],
            ['Dokter Spesialis', 'Menangani kasus medis spesifik lanjutan setelah menyelesaikan pendidikan spesialisasi.'],
            ['Peneliti Medis', 'Melakukan riset biomedis, uji klinis terapi obat baru, dan rekayasa vaksin kesehatan.'],
            ['Arsitek Bangunan', 'Merancang konsep tata ruang, estetika bentuk, dan gambar kerja konstruksi bangunan.'],
            ['BIM Manager', 'Mengintegrasikan permodelan 3D digital arsitektur, struktur sipil, dan mekanikal bangunan.'],
            ['Urban Designer', 'Menata masterplan kawasan perkotaan terpadu, koridor pedestrian, dan taman terbuka hijau.'],
            ['Akuntan Publik', 'Memeriksa independen laporan keuangan dan audit kepatuhan perusahaan di KAP.'],
            ['Konsultan Pajak', 'Merancang strategi kepatuhan pajak korporasi legal dan efisiensi beban fiskal perusahaan.'],
            ['Financial Analyst', 'Mengevaluasi kinerja portofolio emiten pasar modal, proyeksi kas, dan kelayakan investasi.'],
            ['Internal Auditor', 'Mengawasi pengendalian internal operasional dan mitigasi risiko tata kelola Good Corporate Governance.'],
            ['Psikolog Klinis', 'Memberikan asesmen kesehatan mental, konseling terapeutik, dan intervensi psikoterapi.'],
            ['HR Specialist', 'Mengelola rekrutmen talenta, evaluasi performa kerja karyawan, dan program pelatihan SDM.'],
            ['UX Researcher', 'Mengeksplorasi motivasi kognitif dan perilaku psikologis pengguna produk digital.'],
            ['Diplomat', 'Mewakili kepentingan diplomasi bilateral dan multilateral negara di kedutaan luar negeri.'],
            ['International NGO Officer', 'Mengelola program mitigasi kemanusiaan darurat, pengungsi, dan advokasi hak asasi PBB.'],
            ['Political Risk Analyst', 'Menyediakan kajian risiko geopolitik dan kepatuhan regulasi bagi korporasi multinasional.'],
        ];

        $profesiMap = [];
        foreach ($profesiData as [$nama, $deskripsi]) {
            $p = Profesi::firstOrCreate(
                ['nama' => $nama],
                ['deskripsi' => $deskripsi, 'aktif' => 1]
            );
            $profesiMap[$nama] = $p->id_profesi;
        }

        $prodiData = [
            [
                'Teknik Informatika',
                'Teknologi',
                'Program studi rekayasa sistem perangkat lunak modern, algoritma, cloud computing, kecerdasan buatan, dan keamanan siber.',
                'IRC',
                ['Software Engineer', 'DevOps & Cloud Engineer', 'Cybersecurity Analyst', 'Data Scientist'],
            ],
            [
                'Sains Data',
                'Teknologi',
                'Program studi analisis statistika komputasi, machine learning, ekosistem big data, dan visualisasi prediktif.',
                'IRC',
                ['Data Scientist', 'Data Analyst', 'Data Engineer'],
            ],
            [
                'Pendidikan Dokter',
                'Kesehatan',
                'Program studi medis klinis untuk mencetak dokter profesional dengan penguasaan biomedis dan keterampilan diagnosis holistik.',
                'ISR',
                ['Dokter Umum', 'Dokter Spesialis', 'Peneliti Medis'],
            ],
            [
                'Arsitektur',
                'Teknik & Desain',
                'Program studi estetika rancang bangun, sains bangunan ramah lingkungan, komputasi 3D BIM, dan tata ruang perkotaan.',
                'AIR',
                ['Arsitek Bangunan', 'BIM Manager', 'Urban Designer'],
            ],
            [
                'Akuntansi',
                'Ekonomi & Bisnis',
                'Program studi auditing independen, perpajakan korporasi, sistem informasi akuntansi digital ERP, dan analisis keuangan strategis.',
                'CES',
                ['Akuntan Publik', 'Konsultan Pajak', 'Financial Analyst', 'Internal Auditor'],
            ],
            [
                'Psikologi',
                'Sosial Humaniora',
                'Program studi perilaku kognitif manusia, psikometri asesmen kepribadian, konseling suportif, dan manajemen talenta SDM.',
                'SAI',
                ['Psikolog Klinis', 'HR Specialist', 'UX Researcher'],
            ],
            [
                'Hubungan Internasional',
                'Sosial Humaniora',
                'Program studi geopolitik kawasan, negosiasi diplomasi multilateral, hukum global, dan resolusi konflik antarbangsa.',
                'EAS',
                ['Diplomat', 'International NGO Officer', 'Political Risk Analyst'],
            ],
        ];

        foreach ($prodiData as [$nama, $rumpun, $deskripsi, $riasec, $listProfesi]) {
            $hurufAwal = substr($riasec, 0, 1);
            $prodi = ProgramStudi::updateOrCreate(
                ['nama' => $nama],
                [
                    'id_dimensi'       => $dimensiMap[$hurufAwal] ?? 1,
                    'rumpun'           => $rumpun,
                    'tentang'          => $deskripsi,
                    'deskripsi'        => $deskripsi,
                    'kode_riasec'      => $riasec,
                    'aktif'            => 1,
                    'status_validasi'  => 'tervalidasi',
                ]
            );

            $idProfesiList = array_map(fn ($pName) => $profesiMap[$pName] ?? null, $listProfesi);
            $idProfesiList = array_filter($idProfesiList);
            $prodi->profesi()->sync($idProfesiList);
        }

        // ==========================================
        // 6. AKUN PENGGUNA (ADMIN, GURU BK, SISWA)
        // ==========================================

        // 6A. Administrator
        User::firstOrCreate(
            ['username' => 'admin'],
            [
                'password'             => $password,
                'role'                 => 'ADMIN',
                'must_change_password' => 0,
                'aktif'                => 1,
            ]
        );

        // 6B. Guru BK (Bu Rina Wulandari)
        $userGuru = User::firstOrCreate(
            ['username' => 'rina.wulandari'],
            [
                'password'             => $password,
                'role'                 => 'GURU_BK',
                'must_change_password' => 0,
                'aktif'                => 1,
            ]
        );

        $guru = Guru::firstOrCreate(
            ['id_user' => $userGuru->id_user],
            [
                'nip'                => '198503152010012003',
                'nama'               => 'Rina Wulandari, S.Pd.',
                'email'              => 'rina.wulandari@sman1malang.sch.id',
                'no_telepon'         => '081234567890',
                'status_kepegawaian' => 'PNS',
                'agama'              => 'Islam',
                'jenis_kelamin'      => 'P',
                'alamat'             => 'Jl. Tugu No. 1, Kota Malang',
            ]
        );

        // Penugasan BK untuk tingkat X, XI, XII di semester aktif
        foreach (['X', 'XI', 'XII'] as $tingkat) {
            PenugasanBk::firstOrCreate(
                [
                    'id_guru'     => $guru->id_guru,
                    'id_semester' => $semester->id_semester,
                    'tingkat'     => $tingkat,
                ],
                [
                    'jadwal_layanan' => 'Senin - Jumat, 08:00 - 14:00',
                ]
            );
        }

        // ==========================================
        // 7. KELAS (X-1, XI-1, XII-1)
        // ==========================================
        $kelasX = Kelas::firstOrCreate(
            ['nama' => 'X-1', 'id_tahun_ajaran' => $ta->id_tahun_ajaran],
            ['tingkat' => 'X', 'fase' => 'E']
        );
        $kelasXI = Kelas::firstOrCreate(
            ['nama' => 'XI-1', 'id_tahun_ajaran' => $ta->id_tahun_ajaran],
            ['tingkat' => 'XI', 'fase' => 'F']
        );
        $kelasXII = Kelas::firstOrCreate(
            ['nama' => 'XII-1', 'id_tahun_ajaran' => $ta->id_tahun_ajaran],
            ['tingkat' => 'XII', 'fase' => 'F']
        );

        // ==========================================
        // 8. DATA SISWA PER JENJANG
        // ==========================================

        // 8A. Siswa Kelas X (Alya Safitri - Siap Tes)
        $userSiswaX = User::firstOrCreate(
            ['username' => 'siswa.x'],
            [
                'password'             => $password,
                'role'                 => 'SISWA',
                'must_change_password' => 0,
                'aktif'                => 1,
            ]
        );
        $siswaX = Siswa::firstOrCreate(
            ['id_user' => $userSiswaX->id_user],
            [
                'nis'           => '10001',
                'nama'          => 'Alya Safitri',
                'jenis_kelamin' => 'P',
                'agama'         => 'Islam',
                'tahun_masuk'   => 2026,
            ]
        );
        $rkX = RiwayatKelas::firstOrCreate([
            'id_siswa'    => $siswaX->id_siswa,
            'id_kelas'    => $kelasX->id_kelas,
            'id_semester' => $semester->id_semester,
        ]);

        // 8B. Siswa Kelas XI (Bima Arya - Memiliki Hasil Tes Kelas X)
        $userSiswaXI = User::firstOrCreate(
            ['username' => 'siswa.xi'],
            [
                'password'             => $password,
                'role'                 => 'SISWA',
                'must_change_password' => 0,
                'aktif'                => 1,
            ]
        );
        $siswaXI = Siswa::firstOrCreate(
            ['id_user' => $userSiswaXI->id_user],
            [
                'nis'           => '11001',
                'nama'          => 'Bima Arya Nugraha',
                'jenis_kelamin' => 'L',
                'agama'         => 'Islam',
                'tahun_masuk'   => 2025,
            ]
        );
        // Riwayat kelas saat ini (Kelas XI)
        RiwayatKelas::firstOrCreate([
            'id_siswa'    => $siswaXI->id_siswa,
            'id_kelas'    => $kelasXI->id_kelas,
            'id_semester' => $semester->id_semester,
        ]);
        // Tahun ajaran lampau (2025/2026) untuk data historis kelas XI
        $taLampau = TahunAjaran::firstOrCreate(
            ['nama' => '2025/2026'],
            ['aktif' => 0]
        );
        $semesterLampau = Semester::firstOrCreate(
            ['id_tahun_ajaran' => $taLampau->id_tahun_ajaran, 'nama' => 'GENAP'],
            ['aktif' => 0]
        );
        $kelasXLampau = Kelas::firstOrCreate(
            ['nama' => 'X-1 (25/26)', 'id_tahun_ajaran' => $taLampau->id_tahun_ajaran],
            ['tingkat' => 'X', 'fase' => 'E']
        );

        // Riwayat kelas sewaktu Kelas X (Semester lampau)
        $rkX_Bima = RiwayatKelas::firstOrCreate([
            'id_siswa'    => $siswaXI->id_siswa,
            'id_kelas'    => $kelasXLampau->id_kelas,
            'id_semester' => $semesterLampau->id_semester,
        ]);

        // Buat sesi tes lampau kelas X untuk Bima
        $sesiBima = SesiTes::firstOrCreate(
            [
                'id_riwayat_kelas' => $rkX_Bima->id_riwayat_kelas,
                'tingkat'          => 'X',
            ],
            [
                'kode_holland'  => 'IRS',
                'waktu_mulai'   => now()->subMonths(6),
                'waktu_selesai' => now()->subMonths(6),
                'status'        => 'selesai',
            ]
        );
        // Masukkan hasil skor normal 6 dimensi
        $skorBima = ['I' => 90, 'R' => 85, 'S' => 75, 'A' => 60, 'E' => 50, 'C' => 45];
        foreach ($skorBima as $k => $val) {
            HasilTesRiasec::firstOrCreate(
                ['id_sesi_tes' => $sesiBima->id_sesi_tes, 'id_dimensi' => $dimensiMap[$k]],
                ['skor_mentah' => (int)($val / 10), 'skor_normal' => (float)$val]
            );
        }
        // Masukkan 5 rekomendasi mapel pilihan untuk Bima
        $topMapel = Mapel::where('is_pilihan', 1)->take(5)->get();
        foreach ($topMapel as $idx => $m) {
            RekomendasiSepuluh::firstOrCreate(
                ['id_sesi_tes' => $sesiBima->id_sesi_tes, 'id_mapel' => $m->id_mapel],
                ['peringkat' => $idx + 1, 'skor' => 95 - ($idx * 5), 'alasan' => 'Sesuai dengan kode dominan IRS']
            );
        }

        // 8C. Siswa Kelas XII (Citra Maharani - Siap Tes Prodi)
        $userSiswaXII = User::firstOrCreate(
            ['username' => 'siswa.xii'],
            [
                'password'             => $password,
                'role'                 => 'SISWA',
                'must_change_password' => 0,
                'aktif'                => 1,
            ]
        );
        $siswaXII = Siswa::firstOrCreate(
            ['id_user' => $userSiswaXII->id_user],
            [
                'nis'           => '12001',
                'nama'          => 'Citra Maharani',
                'jenis_kelamin' => 'P',
                'agama'         => 'Islam',
                'tahun_masuk'   => 2024,
            ]
        );
        RiwayatKelas::firstOrCreate([
            'id_siswa'    => $siswaXII->id_siswa,
            'id_kelas'    => $kelasXII->id_kelas,
            'id_semester' => $semester->id_semester,
        ]);
    }
}
