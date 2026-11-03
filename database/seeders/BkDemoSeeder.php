<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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
                'role' => 'BK',
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

        // ==========================================
        // 2. DATA KELAS DEMO
        // ==========================================
        $kelasList = [
            ['nama' => 'X-3', 'tingkat' => 'X', 'fase' => 'E', 'tahun_ajaran' => '2025/2026'],
            ['nama' => 'X-B', 'tingkat' => 'X', 'fase' => 'E', 'tahun_ajaran' => '2026/2027'],
            ['nama' => 'X-C', 'tingkat' => 'X', 'fase' => 'E', 'tahun_ajaran' => '2026/2027'],
            ['nama' => 'XI-A', 'tingkat' => 'XI', 'fase' => 'F', 'tahun_ajaran' => '2026/2027'],
            ['nama' => 'XI-C', 'tingkat' => 'XI', 'fase' => 'F', 'tahun_ajaran' => '2026/2027'],
        ];

        foreach ($kelasList as $k) {
            DB::table('kelas')->updateOrInsert(
                ['nama' => $k['nama'], 'tahun_ajaran' => $k['tahun_ajaran']],
                ['tingkat' => $k['tingkat'], 'fase' => $k['fase']]
            );
        }

        $idKelasX3 = DB::table('kelas')->where('nama', 'X-3')->where('tahun_ajaran', '2025/2026')->value('id_kelas');
        $idKelasXB = DB::table('kelas')->where('nama', 'X-B')->value('id_kelas');
        $idKelasXC = DB::table('kelas')->where('nama', 'X-C')->value('id_kelas');
        $idKelasXIA = DB::table('kelas')->where('nama', 'XI-A')->value('id_kelas');
        $idKelasXIC = DB::table('kelas')->where('nama', 'XI-C')->value('id_kelas');

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
            ['id_siswa' => $idSiswaNadia, 'id_kelas' => $idKelasX3],
            ['id_guru' => $idGuru]
        );
        $idRiwayatNadia = DB::table('riwayat_kelas')
            ->where('id_siswa', $idSiswaNadia)
            ->where('id_kelas', $idKelasX3)
            ->value('id_riwayat_kelas');

        // ==========================================
        // 4. HASIL TES RIASEC UNTUK NADIA (IAS)
        // ==========================================
        DB::table('sesi_tes')->updateOrInsert(
            ['id_riwayat_kelas' => $idRiwayatNadia, 'tingkat' => 'X'],
            [
                'kode_holland' => 'IAS',
                'waktu_mulai' => '2025-08-18 08:00:00',
                'waktu_selesai' => '2025-08-18 08:45:00',
            ]
        );
        $idSesiNadia = DB::table('sesi_tes')->where('id_riwayat_kelas', $idRiwayatNadia)->value('id_sesi_tes');

        // Ambil ID Dimensi RIASEC
        $dimensiMap = DB::table('dimensi_riasec')->pluck('id_dimensi', 'kode');

        // Skor Nadia sesuai Mockup Dashboard:
        // R: 72%, I: 91%, A: 84%, S: 76%, E: 65%, C: 58%
        $skorNadia = [
            'R' => 72,
            'I' => 91,
            'A' => 84,
            'S' => 76,
            'E' => 65,
            'C' => 58,
        ];

        foreach ($skorNadia as $kode => $skor) {
            if (isset($dimensiMap[$kode])) {
                DB::table('hasil_tes_riasec')->updateOrInsert(
                    ['id_sesi_tes' => $idSesiNadia, 'id_dimensi' => $dimensiMap[$kode]],
                    ['skor_mentah' => $skor, 'skor_normal' => $skor]
                );
            }
        }

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
                'keluhan' => 'Konsultasi pilihan jurusan',
                'status' => 'Menunggu',
                'created_at' => now()->subMinutes(35),
            ],
            [
                'username' => 'bima.arya',
                'nis' => '2410092',
                'nama' => 'Bima Arya Nugraha',
                'jk' => 'L',
                'kelas_id' => $idKelasXC,
                'keluhan' => 'Motivasi belajar menurun',
                'status' => 'Diproses',
                'created_at' => now()->subHours(2),
            ],
            [
                'username' => 'citra.maharani',
                'nis' => '2310015',
                'nama' => 'Citra Maharani',
                'jk' => 'P',
                'kelas_id' => $idKelasXIA,
                'keluhan' => 'Perencanaan studi lanjut',
                'status' => 'Dijadwalkan',
                'created_at' => now()->subDays(1),
            ],
            [
                'username' => 'farah.nabila',
                'nis' => '2310022',
                'nama' => 'Farah Nabila',
                'jk' => 'P',
                'kelas_id' => $idKelasXIC,
                'keluhan' => 'Bimbingan karir lanjutan',
                'status' => 'Selesai',
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

            if ($item['kelas_id']) {
                DB::table('riwayat_kelas')->updateOrInsert(
                    ['id_siswa' => $sId, 'id_kelas' => $item['kelas_id']],
                    ['id_guru' => $idGuru]
                );
            }

            // Insert Permintaan Konsultasi jika tabel ada
            if (\Illuminate\Support\Facades\Schema::hasTable('permintaan_konsultasi')) {
                DB::table('permintaan_konsultasi')->updateOrInsert(
                    ['id_siswa' => $sId, 'catatan' => $item['keluhan']],
                    ['status' => $item['status'], 'created_at' => $item['created_at']]
                );
            }
        }
    }
}
