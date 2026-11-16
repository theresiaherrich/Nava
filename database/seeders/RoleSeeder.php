<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

/** Idempoten: aman dijalankan berulang di semua lingkungan. */
class RoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (config('siakad.roles') as $kode => $nama) {
            Role::updateOrCreate(['kode' => $kode], ['nama' => $nama]);
        }
    }
}
