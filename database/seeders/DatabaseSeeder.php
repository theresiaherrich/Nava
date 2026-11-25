<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Data referensi: aman untuk semua lingkungan.
        $this->call([RoleSeeder::class, AgamaSeeder::class]);

        $sandiContoh = config('siakad.seed_default_password') ?: config('siakad.demo_password');

        // Data contoh (kelas, guru, siswa, nilai): hanya local/testing dan bila sandi contoh diisi.
        if (app()->environment(['local', 'testing']) && filled($sandiContoh)) {
            $this->call(DevSeeder::class);
        } elseif (app()->environment('local')) {
            $this->command?->warn('Data contoh dilewati: isi NAVA_DEMO_PASSWORD di .env untuk membuat data dan akun contoh.');
        }

        // Satu akun demo per peran: HANYA APP_ENV=local.
        if (app()->environment('local') && filled(config('siakad.demo_password'))) {
            $this->call(DemoAccountSeeder::class);
        }
    }
}
