<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

// BUTUH tabel inti: user, siswa, kelas, riwayat_kelas (milik There).
// Sesuaikan nama kolom kalau migration tabel inti berbeda dari SQL.
class BkDemoSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password123');

        // 1 akun guru BK
        DB::table('user')->updateOrInsert(
            ['username' => 'bk01'],
            ['password' => $password, 'must_change_password' => 0, 'role' => 'BK']
        );

        // 1 kelas demo
        DB::table('kelas')->updateOrInsert(
            ['nama' => 'X-1', 'tahun_ajaran' => '2026/2027'],
            ['tingkat' => 'X', 'fase' => 'E']
        );
        $idKelas = DB::table('kelas')->where('nama', 'X-1')->where('tahun_ajaran', '2026/2027')->value('id_kelas');

        // 5 siswa dummy
        $siswa = [
            ['2601', 'Ahmad Fauzi',    'L'],
            ['2602', 'Bunga Lestari',  'P'],
            ['2603', 'Citra Dewi',     'P'],
            ['2604', 'Dimas Pratama',  'L'],
            ['2605', 'Eka Putri',      'P'],
        ];

        foreach ($siswa as [$nis, $nama, $jk]) {
            DB::table('user')->updateOrInsert(
                ['username' => $nis],
                ['password' => $password, 'must_change_password' => 0, 'role' => 'SISWA']
            );
            $idUser = DB::table('user')->where('username', $nis)->value('id_user');

            DB::table('siswa')->updateOrInsert(
                ['nis' => $nis],
                ['id_user' => $idUser, 'nama' => $nama, 'jenis_kelamin' => $jk, 'tahun_masuk' => 2026]
            );
            $idSiswa = DB::table('siswa')->where('nis', $nis)->value('id_siswa');

            DB::table('riwayat_kelas')->updateOrInsert(
                ['id_siswa' => $idSiswa, 'id_kelas' => $idKelas],
                ['id_guru' => null]
            );
        }
    }
}
