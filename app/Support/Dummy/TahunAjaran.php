<?php

namespace App\Support\Dummy;

/**
 * Data dummy Tahun Ajaran & Semester (desain Kurikulum 4.01–4.02).
 */
class TahunAjaran
{
    /** TODO API: GET /api/kurikulum/tahun-ajaran (beserta semester) */
    public static function semester(): array
    {
        return [
            ['id_semester' => 1, 'tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil', 'mulai' => '15 Jul 2026', 'selesai' => '19 Des 2026', 'rentang' => '15 Juli – 19 Desember 2026', 'status' => 'aktif'],
            ['id_semester' => 2, 'tahun_ajaran' => '2026/2027', 'semester' => 'Genap', 'mulai' => '4 Jan 2027', 'selesai' => '25 Jun 2027', 'rentang' => '4 Januari – 25 Juni 2027', 'status' => 'akan_datang'],
            ['id_semester' => 3, 'tahun_ajaran' => '2025/2026', 'semester' => 'Genap', 'mulai' => '5 Jan 2026', 'selesai' => '26 Jun 2026', 'rentang' => '5 Januari – 26 Juni 2026', 'status' => 'selesai'],
            ['id_semester' => 4, 'tahun_ajaran' => '2025/2026', 'semester' => 'Ganjil', 'mulai' => '14 Jul 2025', 'selesai' => '19 Des 2025', 'rentang' => '14 Juli – 19 Desember 2025', 'status' => 'selesai'],
            ['id_semester' => 5, 'tahun_ajaran' => '2024/2025', 'semester' => 'Genap', 'mulai' => '6 Jan 2025', 'selesai' => '27 Jun 2025', 'rentang' => '6 Januari – 27 Juni 2025', 'status' => 'selesai'],
            ['id_semester' => 6, 'tahun_ajaran' => '2024/2025', 'semester' => 'Ganjil', 'mulai' => '15 Jul 2024', 'selesai' => '20 Des 2024', 'rentang' => '15 Juli – 20 Desember 2024', 'status' => 'selesai'],
        ];
    }

    /** TODO API: GET /api/kurikulum/rapor/ringkasan?semester=aktif (rapor STS terbit per kelas) */
    public static function ringkasanAktivasi(): array
    {
        return [
            'rapor_sts_terbit' => 0,
            'jumlah_kelas' => 30,
            'jumlah_rombel' => 312,
            'jumlah_wali' => 30,
        ];
    }
}
