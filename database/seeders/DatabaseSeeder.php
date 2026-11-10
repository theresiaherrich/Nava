<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * NONAKTIF (data sudah ada di database nava melalui nava_2_data_awal.sql):
     * - DimensiRiasecSeeder::class
     * - SoalRiasecSeeder::class
     *
     * NONAKTIF (digantikan AkunContohSeeder):
     * - BkDemoSeeder::class
     *
     * Untuk pengujian, jalankan: php artisan db:seed --class=AkunContohSeeder
     */
    public function run(): void
    {
        $this->call([]);
    }
}
