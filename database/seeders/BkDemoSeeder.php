<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class BkDemoSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password123');

        // ==========================================
        // 1. DATA GURU BK (Bu Rina)
        // ==========================================
        DB::table('user')->updateOrInsert(
            ['username' => 'rina.wulandari'],
            [
                'password' => $password,
                'must_change_password' => 0,
                'role' => 'GURU',
            ]
        );
        $idUserGuru = DB::table('user')->where('username', 'rina.wulandari')->value('id_user');

        DB::table('guru')->updateOrInsert(
            ['id_user' => $idUserGuru],
            [
                'nip' => '198503152010012003',
                'nama' => 'Rina Wulandari, S.Pd.',
                'status_kepegawaian' => 'PNS',
                'agama' => 'Islam',
                'jenis_kelamin' => 'P',
                'alamat' => 'Kota Malang',
            ]
        );
        $idGuru = DB::table('guru')->where('id_user', $idUserGuru)->value('id_guru');

        DB::table('guru_bk')->updateOrInsert(
            ['id_guru' => $idGuru],
            ['aktif' => true]
        );

        // ==========================================
        // 2. DATA KELAS DEMO
        // ==========================================
        $tahunAjaranList = ['2025/2026', '2026/2027'];
        foreach ($tahunAjaranList as $tahunAjaran) {
            DB::table('tahun_ajaran')->insertOrIgnore(['nama' => $tahunAjaran]);
        }

        $tahunAjaranIds = DB::table('tahun_ajaran')
            ->whereIn('nama', $tahunAjaranList)
            ->pluck('id_tahun_ajaran', 'nama');

        foreach ($tahunAjaranIds as $idTahunAjaran) {
            DB::table('semester')->insertOrIgnore([
                ['id_tahun_ajaran' => $idTahunAjaran, 'nama' => 'GASAL'],
            ]);
        }

        $semesterIds = DB::table('semester')
            ->whereIn('id_tahun_ajaran', $tahunAjaranIds->values())
            ->where('nama', 'GASAL')
            ->pluck('id_semester', 'id_tahun_ajaran');
        $idSemesterAktif = $semesterIds[$tahunAjaranIds['2026/2027']];
        DB::table('semester')->where('id_semester', $idSemesterAktif)->update(['aktif' => true]);

        $kelasList = [
            ['nama' => 'X-3', 'tingkat' => 'X', 'fase' => 'E', 'tahun_ajaran' => '2025/2026'],
            ['nama' => 'X-B', 'tingkat' => 'X', 'fase' => 'E', 'tahun_ajaran' => '2026/2027'],
            ['nama' => 'X-C', 'tingkat' => 'X', 'fase' => 'E', 'tahun_ajaran' => '2026/2027'],
            ['nama' => 'XI-A', 'tingkat' => 'XI', 'fase' => 'F', 'tahun_ajaran' => '2026/2027'],
            ['nama' => 'XI-C', 'tingkat' => 'XI', 'fase' => 'F', 'tahun_ajaran' => '2026/2027'],
        ];

        foreach ($kelasList as $k) {
            $idTahunAjaran = $tahunAjaranIds[$k['tahun_ajaran']];
            DB::table('kelas')->updateOrInsert(
                ['nama' => $k['nama'], 'id_tahun_ajaran' => $idTahunAjaran],
                ['tingkat' => $k['tingkat'], 'fase' => $k['fase']]
            );
        }

        $idKelasX3 = DB::table('kelas')->where('nama', 'X-3')
            ->where('id_tahun_ajaran', $tahunAjaranIds['2025/2026'])->value('id_kelas');
        $idKelasXB = DB::table('kelas')->where('nama', 'X-B')
            ->where('id_tahun_ajaran', $tahunAjaranIds['2026/2027'])->value('id_kelas');
        $idKelasXC = DB::table('kelas')->where('nama', 'X-C')
            ->where('id_tahun_ajaran', $tahunAjaranIds['2026/2027'])->value('id_kelas');
        $idKelasXIA = DB::table('kelas')->where('nama', 'XI-A')
            ->where('id_tahun_ajaran', $tahunAjaranIds['2026/2027'])->value('id_kelas');
        $idKelasXIC = DB::table('kelas')->where('nama', 'XI-C')
            ->where('id_tahun_ajaran', $tahunAjaranIds['2026/2027'])->value('id_kelas');
        $idSemesterNadia = $semesterIds[$tahunAjaranIds['2025/2026']];

        // ==========================================
        // 3. DATA SISWA UTAMA (Nadia Azzahra Putri)
        // ==========================================
        DB::table('user')->updateOrInsert(
            ['username' => 'nadia'],
            [
                'password' => $password,
                'must_change_password' => 0,
                'role' => 'SISWA',
            ]
        );
        $idUserNadia = DB::table('user')->where('username', 'nadia')->value('id_user');

        DB::table('siswa')->updateOrInsert(
            ['nis' => '2410073'],
            [
                'id_user' => $idUserNadia,
                'nisn' => '0071234567',
                'nama' => 'Nadia Azzahra Putri',
                'jenis_kelamin' => 'P',
                'agama' => 'Islam',
                'tahun_masuk' => 2025,
                'alamat' => 'Jl. Tugu Utara No. 1, Malang',
            ]
        );
        $idSiswaNadia = DB::table('siswa')->where('nis', '2410073')->value('id_siswa');

        DB::table('riwayat_kelas')->updateOrInsert(
            ['id_siswa' => $idSiswaNadia, 'id_semester' => $idSemesterNadia],
            ['id_kelas' => $idKelasX3]
        );
        DB::table('riwayat_kelas')->updateOrInsert(
            ['id_siswa' => $idSiswaNadia, 'id_semester' => $idSemesterAktif],
            ['id_kelas' => $idKelasXB]
        );
        $idRiwayatNadia = DB::table('riwayat_kelas')
            ->where('id_siswa', $idSiswaNadia)
            ->where('id_semester', $idSemesterNadia)
            ->value('id_riwayat_kelas');

        // ==========================================
        // 4. HASIL TES RIASEC UNTUK NADIA (IAS)
        // ==========================================
        DB::table('periode_tes')->updateOrInsert(
            ['id_semester' => $idSemesterNadia, 'tingkat' => 'X'],
            [
                'nama' => 'Periode Tes RIASEC Demo',
                'tanggal_mulai' => '2025-08-01',
                'tanggal_selesai' => '2025-08-31',
                'status' => 'aktif',
            ]
        );
        foreach (['X', 'XI'] as $tingkat) {
            DB::table('periode_tes')->updateOrInsert(
                ['id_semester' => $idSemesterAktif, 'tingkat' => $tingkat],
                [
                    'nama' => 'Periode Tes RIASEC Demo',
                    'tanggal_mulai' => '2026-08-01',
                    'tanggal_selesai' => '2026-12-31',
                    'status' => 'aktif',
                ]
            );
        }

        DB::table('sesi_tes')->updateOrInsert(
            ['id_riwayat_kelas' => $idRiwayatNadia],
            [
                'skor_r' => 72,
                'skor_i' => 91,
                'skor_a' => 84,
                'skor_s' => 76,
                'skor_e' => 65,
                'skor_c' => 58,
                'kode_holland' => 'IAS',
                'waktu_selesai' => '2025-08-18 08:45:00',
            ]
        );

        // ==========================================
        // 5. SISWA LAIN & ANTREAN BIMBINGAN (Untuk Dashboard Bu Rina)
        // ==========================================
        $antreanSiswa = [
            [
                'username' => 'alya.safitri',
                'nis' => '2410081',
                'nama' => 'Alya Safitri',
                'jk' => 'P',
                'kelas_id' => $idKelasXB,
                'semester_id' => $semesterIds[$tahunAjaranIds['2026/2027']],
                'keluhan' => 'Konsultasi pilihan jurusan',
                'status' => 'Menunggu',
                'balasan_bk' => null,
                'jadwal_bimbingan' => null,
                'created_at' => now()->subMinutes(35),
            ],
            [
                'username' => 'bima.arya',
                'nis' => '2410092',
                'nama' => 'Bima Arya Nugraha',
                'jk' => 'L',
                'kelas_id' => $idKelasXC,
                'semester_id' => $semesterIds[$tahunAjaranIds['2026/2027']],
                'keluhan' => 'Motivasi belajar menurun',
                'status' => 'Diproses',
                'balasan_bk' => null,
                'jadwal_bimbingan' => null,
                'created_at' => now()->subHours(2),
            ],
            [
                'username' => 'citra.maharani',
                'nis' => '2310015',
                'nama' => 'Citra Maharani',
                'jk' => 'P',
                'kelas_id' => $idKelasXIA,
                'semester_id' => $semesterIds[$tahunAjaranIds['2026/2027']],
                'keluhan' => 'Perencanaan studi lanjut',
                'status' => 'Dijadwalkan',
                'balasan_bk' => 'Sesi bimbingan telah dijadwalkan.',
                'jadwal_bimbingan' => now()->addDay(),
                'created_at' => now()->subDays(1),
            ],
            [
                'username' => 'farah.nabila',
                'nis' => '2310022',
                'nama' => 'Farah Nabila',
                'jk' => 'P',
                'kelas_id' => $idKelasXIC,
                'semester_id' => $semesterIds[$tahunAjaranIds['2026/2027']],
                'keluhan' => 'Bimbingan karir lanjutan',
                'status' => 'Selesai',
                'balasan_bk' => 'Sesi bimbingan telah selesai.',
                'jadwal_bimbingan' => null,
                'created_at' => now()->subDays(2),
            ],
        ];

        foreach ($antreanSiswa as $item) {
            DB::table('user')->updateOrInsert(
                ['username' => $item['username']],
                ['password' => $password, 'must_change_password' => 0, 'role' => 'SISWA']
            );
            $uId = DB::table('user')->where('username', $item['username'])->value('id_user');

            DB::table('siswa')->updateOrInsert(
                ['nis' => $item['nis']],
                [
                    'id_user' => $uId,
                    'nama' => $item['nama'],
                    'jenis_kelamin' => $item['jk'],
                    'agama' => 'Islam',
                    'tahun_masuk' => 2025,
                ]
            );
            $sId = DB::table('siswa')->where('nis', $item['nis'])->value('id_siswa');

            $idRiwayatKelas = null;
            if ($item['kelas_id']) {
                DB::table('riwayat_kelas')->updateOrInsert(
                    ['id_siswa' => $sId, 'id_semester' => $item['semester_id']],
                    ['id_kelas' => $item['kelas_id']]
                );
                $idRiwayatKelas = DB::table('riwayat_kelas')
                    ->where('id_siswa', $sId)
                    ->where('id_semester', $item['semester_id'])
                    ->value('id_riwayat_kelas');
            }

            if ($idRiwayatKelas && Schema::hasTable('pengajuan_bk')) {
                DB::table('pengajuan_bk')->updateOrInsert(
                    ['id_riwayat_kelas' => $idRiwayatKelas, 'topik' => $item['keluhan']],
                    [
                        'deskripsi' => $item['keluhan'],
                        'status' => strtolower($item['status']),
                        'id_guru' => $idGuru,
                        'balasan_bk' => $item['balasan_bk'],
                        'jadwal_bimbingan' => $item['jadwal_bimbingan'],
                        'created_at' => $item['created_at'],
                    ]
                );
            } elseif (Schema::hasTable('permintaan_konsultasi')) {
                DB::table('permintaan_konsultasi')->updateOrInsert(
                    ['id_siswa' => $sId, 'catatan' => $item['keluhan']],
                    ['status' => $item['status'], 'created_at' => $item['created_at']]
                );
            }
        }
    }
}
