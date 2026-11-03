<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DimensiRiasecSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['R', 'Realistic',     'Suka kegiatan praktis, teknis, dan bekerja dengan alat atau alam.'],
            ['I', 'Investigative', 'Suka meneliti, menganalisis, dan memecahkan masalah ilmiah.'],
            ['A', 'Artistic',      'Suka berekspresi, kreatif, dan bebas berimajinasi.'],
            ['S', 'Social',        'Suka membantu, mengajar, dan bekerja dengan orang lain.'],
            ['E', 'Enterprising',  'Suka memimpin, memengaruhi, dan berwirausaha.'],
            ['C', 'Conventional',  'Suka keteraturan, data, dan pekerjaan administratif.'],
        ];

        foreach ($data as [$kode, $nama, $deskripsi]) {
            DB::table('dimensi_riasec')->updateOrInsert(
                ['kode' => $kode],
                ['nama' => $nama, 'deskripsi' => $deskripsi]
            );
        }
    }
}
