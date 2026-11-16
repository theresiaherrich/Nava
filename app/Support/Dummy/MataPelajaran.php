<?php

namespace App\Support\Dummy;

/**
 * Data dummy Mata Pelajaran per fase (desain Kurikulum 4.03–4.04).
 */
class MataPelajaran
{
    public const KELOMPOK = [
        'wajib' => 'Mata Pelajaran Wajib',
        'pilihan' => 'Mata Pelajaran Pilihan',
        'bahasa_asing' => 'Bahasa Asing Pilihan',
        'mulok' => 'Muatan Lokal',
        'agama' => 'Pendidikan Agama',
    ];

    /** TODO API: GET /api/kurikulum/mapel */
    public static function semua(): array
    {
        $f = [
            ['WJB-01', 'Pendidikan Agama dan Budi Pekerti', 'wajib', '5 jenis'],
            ['WJB-02', 'Pendidikan Pancasila', 'wajib', 20],
            ['WJB-03', 'Bahasa Indonesia', 'wajib', 20],
            ['WJB-04', 'Matematika (Umum)', 'wajib', 20],
            ['WJB-05', 'Bahasa Inggris', 'wajib', 20],
            ['WJB-06', 'PJOK', 'wajib', 20],
            ['WJB-07', 'Sejarah', 'wajib', 20],
            ['WJB-08', 'Seni dan Budaya', 'wajib', 20],
            ['PIL-01', 'Prakarya dan Kewirausahaan', 'pilihan', 6],
            ['PIL-02', 'Ekonomi', 'pilihan', 8],
            ['PIL-03', 'Geografi', 'pilihan', 8],
            ['PIL-04', 'Bahasa Indonesia Tingkat Lanjut', 'pilihan', 4],
            ['BA-01', 'Bahasa Jerman', 'bahasa_asing', 4],
            ['BA-02', 'Bahasa Jepang', 'bahasa_asing', 4],
            ['BA-03', 'Bahasa Mandarin', 'bahasa_asing', 6],
            ['BA-04', 'Bahasa Arab', 'bahasa_asing', 2],
            ['MLK-01', 'Bahasa Daerah (Bahasa Jawa)', 'mulok', 20],
            ['AGM-01', 'Pendidikan Agama Islam dan Budi Pekerti', 'agama', 20],
            ['AGM-02', 'Pendidikan Agama Kristen dan Budi Pekerti', 'agama', 2],
            ['AGM-03', 'Pendidikan Agama Katolik dan Budi Pekerti', 'agama', 2],
            ['AGM-04', 'Pendidikan Agama Hindu dan Budi Pekerti', 'agama', 1],
            ['AGM-05', 'Pendidikan Agama Buddha dan Budi Pekerti', 'agama', 1],
        ];

        $e = [
            ['WJB-01', 'Pendidikan Agama dan Budi Pekerti', 'wajib', '5 jenis'],
            ['WJB-02', 'Pendidikan Pancasila', 'wajib', 10],
            ['WJB-03', 'Bahasa Indonesia', 'wajib', 10],
            ['WJB-04', 'Matematika', 'wajib', 10],
            ['WJB-05', 'Bahasa Inggris', 'wajib', 10],
            ['WJB-06', 'PJOK', 'wajib', 10],
            ['WJB-07', 'Sejarah', 'wajib', 10],
            ['WJB-08', 'Seni dan Budaya', 'wajib', 10],
            ['WJB-09', 'Informatika', 'wajib', 10],
            ['WJB-10', 'Ilmu Pengetahuan Alam (Fisika, Kimia, Biologi)', 'wajib', 30],
            ['WJB-11', 'Ilmu Pengetahuan Sosial', 'wajib', 10],
            ['BA-01', 'Bahasa Jerman', 'bahasa_asing', 2],
            ['BA-02', 'Bahasa Jepang', 'bahasa_asing', 2],
            ['BA-03', 'Bahasa Mandarin', 'bahasa_asing', 3],
            ['BA-04', 'Bahasa Arab', 'bahasa_asing', 1],
            ['MLK-01', 'Bahasa Daerah (Bahasa Jawa)', 'mulok', 10],
            ['AGM-01', 'Pendidikan Agama Islam dan Budi Pekerti', 'agama', 10],
            ['AGM-02', 'Pendidikan Agama Kristen dan Budi Pekerti', 'agama', 1],
            ['AGM-03', 'Pendidikan Agama Katolik dan Budi Pekerti', 'agama', 1],
            ['AGM-04', 'Pendidikan Agama Hindu dan Budi Pekerti', 'agama', 1],
            ['AGM-05', 'Pendidikan Agama Buddha dan Budi Pekerti', 'agama', 1],
        ];

        $map = fn (array $baris, string $fase) => array_map(fn ($m) => [
            'id_mapel' => strtolower($fase.'-'.$m[0]),
            'kode' => $m[0],
            'nama' => $m[1],
            'kelompok' => $m[2],
            'fase' => $fase,
            'jumlah_rombel' => $m[3],
            'aktif' => true,
        ], $baris);

        return [...$map($e, 'E'), ...$map($f, 'F')];
    }
}
