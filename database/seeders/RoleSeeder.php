<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Label mengikuti desain: "Operator" = ADMIN, "Guru BK" = BUDAYA_POSITIF (modul A3).
        $roles = [
            ['kode' => 'ADMIN', 'nama' => 'Operator'],
            ['kode' => 'KURIKULUM', 'nama' => 'Admin Kurikulum'],
            ['kode' => 'KESISWAAN', 'nama' => 'Admin Kesiswaan'],
            ['kode' => 'GURU', 'nama' => 'Guru'],
            ['kode' => 'WALI_KELAS', 'nama' => 'Wali Kelas'],
            ['kode' => 'BUDAYA_POSITIF', 'nama' => 'Guru BK'],
            ['kode' => 'SISWA', 'nama' => 'Siswa'],
            ['kode' => 'ORANG_TUA', 'nama' => 'Orang Tua'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['kode' => $role['kode']], ['nama' => $role['nama']]);
        }
    }
}
