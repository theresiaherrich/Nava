<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SoalRiasecSeeder extends Seeder
{
    public function run(): void
    {
        // Soal dummy untuk demo: 3 per dimensi. Ganti dengan soal resmi dari PRD nanti.
        $soal = [
            'R' => [
                'Saya senang memperbaiki barang yang rusak.',
                'Saya suka bekerja dengan alat atau mesin.',
                'Saya lebih suka kegiatan di luar ruangan daripada duduk di kelas.',
            ],
            'I' => [
                'Saya suka mencari tahu penyebab suatu masalah.',
                'Saya senang melakukan percobaan atau penelitian.',
                'Saya suka memecahkan soal logika atau teka-teki.',
            ],
            'A' => [
                'Saya senang menggambar, menulis, atau bermusik.',
                'Saya suka mencoba ide-ide baru yang tidak biasa.',
                'Saya merasa bebas ketika bisa mengekspresikan diri.',
            ],
            'S' => [
                'Saya senang membantu teman yang kesulitan.',
                'Saya suka mengajari orang lain.',
                'Saya mudah memahami perasaan orang lain.',
            ],
            'E' => [
                'Saya suka memimpin kelompok.',
                'Saya senang meyakinkan orang lain akan ide saya.',
                'Saya tertarik memulai usaha sendiri.',
            ],
            'C' => [
                'Saya suka merapikan dan mengatur data atau catatan.',
                'Saya nyaman mengikuti aturan dan prosedur yang jelas.',
                'Saya teliti dalam mengerjakan tugas yang detail.',
            ],
        ];

        $urutan = 1;
        foreach ($soal as $kode => $daftar) {
            $idDimensi = DB::table('dimensi_riasec')->where('kode', $kode)->value('id_dimensi');
            foreach ($daftar as $teks) {
                DB::table('soal_riasec')->updateOrInsert(
                    ['id_dimensi' => $idDimensi, 'teks' => $teks],
                    ['urutan' => $urutan++]
                );
            }
        }
    }
}
