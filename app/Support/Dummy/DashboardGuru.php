<?php

namespace App\Support\Dummy;

/**
 * Data dummy Dashboard Guru Mapel (desain Guru 01 & 05).
 */
class DashboardGuru
{
    /** TODO API: GET /api/guru/dashboard */
    public static function data(bool $kosong = false): array
    {
        $kelas = $kosong ? [] : [
            ['id_rombel' => 'mtk-x-a', 'mapel' => 'Matematika', 'kelas' => 'X-A', 'jumlah_tp' => 6, 'jumlah_siswa' => 32, 'terisi' => 100, 'status' => 'terkirim'],
            ['id_rombel' => 'mtk-x-b', 'mapel' => 'Matematika', 'kelas' => 'X-B', 'jumlah_tp' => 6, 'jumlah_siswa' => 32, 'terisi' => 94, 'status' => 'sedang_diisi'],
            ['id_rombel' => 'mtk-x-c', 'mapel' => 'Matematika', 'kelas' => 'X-C', 'jumlah_tp' => 0, 'jumlah_siswa' => 32, 'terisi' => 0, 'status' => 'belum_ada_tp'],
            ['id_rombel' => 'mtk-xi-a', 'mapel' => 'Matematika', 'kelas' => 'XI-A', 'jumlah_tp' => 10, 'jumlah_siswa' => 32, 'terisi' => 96, 'status' => 'perlu_revisi'],
            ['id_rombel' => 'mtk-xi-b', 'mapel' => 'Matematika', 'kelas' => 'XI-B', 'jumlah_tp' => 0, 'jumlah_siswa' => 32, 'terisi' => 0, 'status' => 'belum_mulai'],
            ['id_rombel' => 'mtk-xi-c', 'mapel' => 'Matematika', 'kelas' => 'XI-C', 'jumlah_tp' => 5, 'jumlah_siswa' => 32, 'terisi' => 100, 'status' => 'disetujui'],
        ];

        return [
            'guru' => ['sapaan' => 'Bu Siti', 'mapel' => 'Matematika'],
            'hari_ini' => 'Senin, 12 Oktober 2026',
            // TODO API: GET /api/kurikulum/periode?status=dibuka
            'periode' => [
                'judul' => 'Periode STS dibuka',
                'keterangan' => 'Isi nilai TP sampai Jumat, 23 Oktober 2026. Setelah itu nilai dikunci untuk Rapor STS.',
            ],
            'statistik' => $kosong ? [
                'rombel' => 0, 'rombel_ket' => 'belum ada pembagian mengajar',
                'tp' => 0, 'tp_maks' => null, 'tp_ket' => 'dari 0 slot TP',
                'terisi' => 0,
                'revisi' => 0, 'revisi_ket' => 'tidak ada',
            ] : [
                'rombel' => 6, 'rombel_ket' => 'X-A, X-B, X-C, XI-A, XI-B, XI-C',
                'tp' => 27, 'tp_maks' => 60, 'tp_ket' => 'maks. 10 TP per rombel',
                'terisi' => 58,
                'revisi' => 1, 'revisi_ket' => 'XI-A · dari wali kelas',
            ],
            'semester' => ['Semester Ganjil 2026/2027', 'Semester Genap 2025/2026', 'Semester Ganjil 2025/2026'],
            'kelas' => $kelas,
            // TODO API: GET /api/notifikasi?limit=3
            'aktivitas' => $kosong ? [] : [
                ['jenis' => 'revisi', 'judul' => 'XI-A dikembalikan oleh wali kelas', 'waktu' => '11 Okt 2026'],
                ['jenis' => 'terkirim', 'judul' => 'X-A dikirim ke wali kelas', 'waktu' => '10 Okt 2026'],
                ['jenis' => 'disetujui', 'judul' => 'XI-C disetujui wali kelas', 'waktu' => '9 Okt 2026'],
            ],
        ];
    }
}
