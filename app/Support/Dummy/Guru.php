<?php

namespace App\Support\Dummy;

/**
 * Data dummy Guru Mapel (desain Guru 06–33): rombel yang diampu, TP, dan nilai.
 * Aturan desain: setengah TP pertama (floor n/2) masuk STS; satu TP ditandai "TP terakhir" (SAS); maks. 10 TP.
 */
class Guru
{
    public const MAKS_TP = 10;

    private const TP_E = [
        'Menjelaskan sifat-sifat bilangan berpangkat dan bentuk akar',
        'Menerapkan barisan dan deret aritmetika dalam masalah kontekstual',
        'Menyelesaikan sistem persamaan linear tiga variabel',
        'Menganalisis pertidaksamaan linear dua variabel',
        'Menginterpretasi karakteristik fungsi kuadrat',
        'Menyelesaikan masalah dengan pemodelan fungsi kuadrat',
    ];

    private const TP_F = [
        'Menjelaskan sifat-sifat bilangan berpangkat dan bentuk akar',
        'Menerapkan barisan dan deret aritmetika dalam masalah kontekstual',
        'Menyelesaikan sistem persamaan linear tiga variabel',
        'Menganalisis pertidaksamaan linear dua variabel',
        'Menginterpretasi karakteristik fungsi kuadrat',
        'Menyelesaikan masalah dengan pemodelan fungsi kuadrat',
        'Menerapkan konsep peluang kejadian majemuk',
        'Menyajikan dan menafsirkan data statistika',
        'Menggunakan trigonometri pada segitiga siku-siku',
        'Memecahkan masalah vektor pada bidang',
    ];

    /** Singkatan materi per TP untuk kepala kolom Input Nilai (desain 19). */
    public const SINGKAT = ['Eksponen', 'Barisan', 'SPLTV', 'Pertidaksamaan', 'F. Kuadrat', 'Pemodelan', 'Peluang', 'Statistika', 'Trigonometri', 'Vektor'];

    /** TODO API: GET /api/guru/rombel (beserta TP) */
    public static function rombel(): array
    {
        $tp = function (array $teks, int $n, array $dinilai = []) {
            return array_map(fn ($i) => [
                'id' => 'tp-'.($i + 1),
                'deskripsi' => $teks[$i],
                'sas' => $i === $n - 1,
                'jumlah_nilai' => in_array($i, $dinilai, true) ? 30 : 0,
            ], range(0, $n - 1));
        };

        return [
            ['id' => 'mtk-x-a', 'kelas' => 'X-A', 'fase' => 'E', 'jumlah_siswa' => 32, 'campuran' => false, 'tp' => $tp(self::TP_E, 6, [0, 1, 2])],
            ['id' => 'mtk-x-b', 'kelas' => 'X-B', 'fase' => 'E', 'jumlah_siswa' => 32, 'campuran' => false, 'tp' => $tp(self::TP_E, 6, [0, 1, 2])],
            ['id' => 'mtk-x-c', 'kelas' => 'X-C', 'fase' => 'E', 'jumlah_siswa' => 32, 'campuran' => false, 'tp' => []],
            ['id' => 'mtk-xi-a', 'kelas' => 'XI-A', 'fase' => 'F', 'jumlah_siswa' => 32, 'campuran' => false, 'tp' => $tp(self::TP_F, 10, [0, 1, 2, 3, 4])],
            ['id' => 'mtk-xi-b', 'kelas' => 'XI-B', 'fase' => 'F', 'jumlah_siswa' => 32, 'campuran' => false, 'tp' => []],
            ['id' => 'mtk-xi-c', 'kelas' => 'XI-C', 'fase' => 'F', 'jumlah_siswa' => 32, 'campuran' => false, 'tp' => $tp(self::TP_F, 5, [0, 1, 2, 3, 4])],
        ];
    }

    /** Nilai X-B sesuai desain 19 (TP1–TP6; null = belum diisi, 105 = contoh tidak valid). */
    private const NILAI_XB = [
        ['0081234561', 'Adinda Putri', [80, 74, 82, 90, 71, 72]],
        ['0081234562', 'Bagas Pratama', [87, 73, 81, 88, 71, 86]],
        ['0081234567', 'Citra Lestari', [76, 71, 72, 83, 70, 72]],
        ['0081234564', 'Dimas Saputra', [71, 72, 81, 83, 92, 86]],
        ['0081234565', 'Eka Wulandari', [73, 77, 80, 90, 78, 71]],
        ['0081234566', 'Fajar Nugroho', [70, 72, 74, 71, 77, 71]],
        ['0081234568', 'Gita Maharani', [87, 74, 76, 83, 74, 87]],
        ['0081234569', 'Hendra Wijaya', [75, 85, 79, 87, 81, 75]],
        ['0081234570', 'Indah Permata', [72, 85, 88, 90, 75, 81]],
        ['0081234571', 'Joko Susilo', [70, 74, 72, 72, 76, 71]],
        ['0081234572', 'Kurnia Sari', [89, 76, 83, 81, 87, 83]],
        ['0081234573', 'Lukman Hakim', [84, 80, 84, 81, 84, 105]],
        ['0081234574', 'Maya Anggraini', [79, 77, 80, 79, 80, null]],
    ];

    /**
     * Peserta + nilai per TP satu rombel. TODO API: GET /api/guru/rombel/{id}/nilai?periode=
     *
     * @return list<array{nisn:string,nama:string,kelas:string,nilai:list<int|null>}>
     */
    public static function peserta(array $rombel): array
    {
        $jumlahTp = count($rombel['tp']);
        if ($jumlahTp === 0) {
            return [];
        }

        $isi = fn (int $i) => array_map(fn ($j) => 70 + (($i * 7 + $j * 5) % 24), range(0, $jumlahTp - 1));

        if (! empty($rombel['campuran'])) {
            $xa = ['Nabila Azzahra', 'Raka Aditya', 'Salma Fauziah', 'Teguh Prakoso', 'Ulfa Rahmani', 'Vino Pratama', 'Wildan Akbar', 'Yasmin Putri', 'Zidan Maulana', 'Arif Rahman', 'Bunga Citra', 'Daffa Putra', 'Elvira Sari', 'Ghani Pratama'];
            $baris = [];
            foreach ($xa as $i => $n) {
                $nilai = $isi($i);
                if (in_array($n, ['Nabila Azzahra', 'Teguh Prakoso'], true)) {
                    $nilai[$jumlahTp - 1] = null;
                }
                $baris[] = ['nisn' => sprintf('00812300%02d', 11 + $i), 'nama' => $n, 'kelas' => 'X-A', 'nilai' => $nilai];
            }
            $xb = [...self::NILAI_XB, ['0081234575', 'Nanda Pratiwi', [78, 75, 80, 82, 79, 80]]];
            foreach (array_slice($xb, 0, 14) as $i => [$nisn, $n, $v]) {
                $baris[] = ['nisn' => $nisn, 'nama' => $n, 'kelas' => 'X-B', 'nilai' => array_map(fn ($x) => $x === null || $x > 100 ? 80 : $x, $v)];
            }

            return $baris;
        }

        $baris = [];
        if ($rombel['kelas'] === 'X-B') {
            foreach (self::NILAI_XB as [$nisn, $n, $v]) {
                $baris[] = ['nisn' => $nisn, 'nama' => $n, 'kelas' => 'X-B', 'nilai' => array_slice($v, 0, $jumlahTp)];
            }
        }
        $sudah = array_column($baris, 'nama');
        foreach (Operator::siswa()->where('kelas', $rombel['kelas'])->values() as $i => $s) {
            if (count($baris) >= $rombel['jumlah_siswa']) {
                break;
            }
            if (in_array($s['nama'], $sudah, true) || $s['nama'] === 'Citra Dewi') {
                continue;
            }
            $baris[] = ['nisn' => $s['nisn'], 'nama' => $s['nama'], 'kelas' => $rombel['kelas'], 'nilai' => $isi($i)];
        }

        return $baris;
    }

    /** Rombel campuran (desain 21). */
    public static function rombelCampuran(): array
    {
        return [
            'id' => 'mtl-1', 'kelas' => 'Matematika TL · Rombel 1', 'judul' => 'Matematika Tingkat Lanjut · Rombel 1', 'fase' => 'E',
            'jumlah_siswa' => 28, 'campuran' => true, 'kelas_asal' => [
                ['kelas' => 'X-A', 'jumlah' => 14, 'status' => 'draf'],
                ['kelas' => 'X-B', 'jumlah' => 14, 'status' => 'disetujui'],
            ],
            'tp' => array_map(fn ($i) => ['id' => 'tp-'.($i + 1), 'deskripsi' => self::TP_E[$i], 'sas' => $i === 5, 'jumlah_nilai' => 28], range(0, 5)),
        ];
    }

    public static function cariRombel(?string $id, string $bawaan = 'mtk-x-b'): array
    {
        $semua = self::rombel();

        return collect($semua)->firstWhere('id', $id) ?? collect($semua)->firstWhere('id', $bawaan);
    }
}
