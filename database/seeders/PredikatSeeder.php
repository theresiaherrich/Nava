<?php

namespace Database\Seeders;

use App\Models\Predikat;
use Illuminate\Database\Seeder;

/**
 * Master predikat ekskul. Rentang nilai sengaja null sampai dikonfirmasi Kesiswaan (panduan 7.4).
 * Aman dijalankan berulang: tidak menimpa baris yang sudah ada.
 */
class PredikatSeeder extends Seeder
{
    public function run(): void
    {
        $keterangan = ['SB' => 'Sangat Baik', 'B' => 'Baik', 'K' => 'Kurang'];
        foreach ($keterangan as $kode => $teks) {
            Predikat::firstOrCreate(['predikat' => $kode], ['keterangan' => $teks, 'aktif' => true]);
        }
    }
}
