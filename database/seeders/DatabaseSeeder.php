<?php

namespace Database\Seeders;

use App\Models\Kelas;
use App\Models\Role;
use App\Models\Semester;
use App\Models\Sekolah;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        Sekolah::firstOrCreate(['nama' => 'SMA Negeri 1 Kota Malang'], [
            'alamat' => 'Jl. Tugu Utara No. 1, Kota Malang',
        ]);

        $tahun = TahunAjaran::firstOrCreate(['nama' => '2026/2027'], ['aktif' => true]);

        $gasal = Semester::firstOrCreate(
            ['id_tahun_ajaran' => $tahun->id_tahun_ajaran, 'nama' => 'GASAL'],
            ['aktif' => true],
        );
        Semester::firstOrCreate(
            ['id_tahun_ajaran' => $tahun->id_tahun_ajaran, 'nama' => 'GENAP'],
            ['aktif' => false],
        );

        foreach (['X-A', 'X-B'] as $nama) {
            Kelas::firstOrCreate(
                ['nama' => $nama, 'id_tahun_ajaran' => $tahun->id_tahun_ajaran],
                ['tingkat' => 'X', 'fase' => 'E'],
            );
        }

        // Akun contoh untuk development. GANTI kata sandinya sebelum dipakai bersama.
        $this->akun('operator', 'Operator Sekolah', 'ADMIN');
        $this->akun('kurikulum', 'Admin Kurikulum', 'KURIKULUM');
    }

    private function akun(string $username, string $nama, string $kodeRole): void
    {
        $user = User::firstOrCreate(['username' => $username], [
            'nama' => $nama,
            'password' => 'password',
            'status_akun' => 'aktif',
            'must_change_password' => true,
        ]);

        $user->roles()->syncWithoutDetaching([Role::where('kode', $kodeRole)->value('id_role')]);
    }
}
