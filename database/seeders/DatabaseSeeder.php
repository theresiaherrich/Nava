<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Data referensi: aman untuk semua lingkungan.
        $this->call([RoleSeeder::class, AgamaSeeder::class]);
    }
}
