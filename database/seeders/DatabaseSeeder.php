<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Data referensi: aman untuk semua lingkungan.
        $this->call([RoleSeeder::class, AgamaSeeder::class]);

        // Data contoh: hanya local/testing dan bila SEED_DEFAULT_PASSWORD diisi.
        if (app()->environment(['local', 'testing']) && filled(config('siakad.seed_default_password'))) {
            $this->call(DevSeeder::class);
        } elseif (app()->environment('local')) {
            $this->command?->warn('Data contoh dilewati: isi SEED_DEFAULT_PASSWORD di .env untuk membuat akun contoh.');
        }
    }
}
