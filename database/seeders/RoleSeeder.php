<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['admin', 'kurikulum', 'guru', 'wali_kelas', 'siswa', 'wali_siswa', 'kepala_sekolah'] as $name) {
            Role::firstOrCreate(['name' => $name]);
        }
    }
}
