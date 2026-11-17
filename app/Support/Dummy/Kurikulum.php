<?php

namespace App\Support\Dummy;

use Illuminate\Support\Collection;

/**
 * Data dummy modul Kurikulum: status penilaian per kelas (dipakai Dashboard 3.01,
 * Periode & Penguncian 5.01, Rekap DKN 6.01), periode, dan KKTP (5.03).
 *
 * Status mapel: d = disetujui, k = dikirim (menunggu verifikasi), s = sedang diisi, b = belum ada TP.
 */
class Kurikulum
{
    /** 16 mapel kelas; 9 pertama ditampilkan di DKN (sisanya "+7"). */
    public const MAPEL = [
        ['Matematika', 'Mat', 'Siti Rahmawati, S.Pd.'],
        ['Bahasa Indonesia', 'B.Ind', 'Dra. Endang Lestari'],
        ['Bahasa Inggris', 'B.Ing', 'Agus Prasetyo, S.Pd.'],
        ['Fisika', 'Fis', 'Rudi Hartono, M.Pd.'],
        ['Biologi', 'Bio', 'Nur Aini, S.Si.'],
        ['Kimia', 'Kim', 'Bambang Sutrisno, S.Pd.'],
        ['Sejarah', 'Sej', 'Hadi Santoso, S.Pd.'],
        ['Pendidikan Pancasila', 'PPKn', 'Teguh Prakoso, M.Pd.'],
        ['Informatika', 'Inf', 'Dimas Aditya, S.Kom.'],
        ['PJOK', 'PJOK', 'Ahmad Fauzi, S.Ag.'],
        ['Seni Budaya', 'SBd', 'Rina Marlina, S.Sos.'],
        ['Ekonomi', 'Eko', 'Dewi Kusuma, S.E.'],
        ['Geografi', 'Geo', 'Wahyu Setiawan, S.Pd.'],
        ['Sosiologi', 'Sos', 'Puji Lestari, S.Pd.'],
        ['Bahasa Jawa', 'B.Jw', 'Sri Wahyuni, S.Pd.'],
        ['Pendidikan Agama', 'PAg', 'Ahmad Fauzi, S.Ag.'],
    ];

    private static ?Collection $kelas = null;

    /** TODO API: GET /api/kurikulum/periode/{id}/ringkasan-kelas */
    public static function statusKelas(): Collection
    {
        return self::$kelas ??= self::bangun();
    }

    private static function bangun(): Collection
    {
        $wali = WaliKelas::waliPerKelas();

        // Kelas X sesuai desain 3.01 / 5.01 / 6.01 (9 mapel tampil, 7 lainnya disetujui kecuali disebut).
        $x = [
            'A' => ['ddddddddd', 16, 100, 'terbuka', 'belum'],
            'B' => ['ddddddddd', 16, 100, 'terkunci', 'pdf'],
            'C' => ['ddkdsdddb', 14, 88, 'terbuka', 'belum'],
            'D' => ['kdsdskdss', 10, 63, 'terbuka', 'belum'],
            'E' => ['sdddddddk', 15, 94, 'koreksi', 'belum'],
            'F' => ['ddddddddd', 16, 100, 'terbuka', 'belum'],
            'G' => ['ddddddddd', 16, 100, 'terkunci', 'pdf'],
            'H' => ['ddddddddd', 16, 100, 'terkunci', 'pdf'],
            'I' => ['dkdsddkds', 12, 75, 'terbuka', 'belum'],
            'J' => ['ddddddddd', 16, 100, 'terkunci', 'belum'],
        ];

        $baris = [];
        foreach (Operator::TINGKAT as $t) {
            foreach (Operator::KELAS as $i => $k) {
                $nama = "$t-$k";
                if ($t === 'X') {
                    [$mapel, $disetujui, $lengkap, $kunci, $rapor] = $x[$k];
                } else {
                    // XI & XII: 4 kelas siap dikunci (sesuai ringkasan dashboard), sisanya masih berjalan.
                    $siap = in_array($nama, ['XI-B', 'XI-F', 'XII-C', 'XII-H'], true);
                    $disetujui = $siap ? 16 : 11 + (($i * 3 + strlen($t)) % 5);
                    $mapel = $siap ? 'ddddddddd' : substr(str_repeat('ddkdsdkdd', 2), $i % 5, 9);
                    $lengkap = $siap ? 100 : (int) round(70 + $disetujui * 1.6);
                    $kunci = 'terbuka';
                    $rapor = 'belum';
                }

                $verifikasi = $disetujui === 16 ? 'selesai' : ($disetujui <= 10 ? 'belum' : 'berjalan');
                $baris[] = [
                    'kelas' => $nama,
                    'tingkat' => $t,
                    'wali' => $wali[$nama] ?? null,
                    'jumlah_siswa' => 32,
                    'mapel' => array_map(fn ($s, $m) => ['singkat' => $m[1], 'status' => $s], str_split($mapel), array_slice(self::MAPEL, 0, 9)),
                    'disetujui' => $disetujui,
                    'kelengkapan' => $lengkap,
                    'verifikasi' => $verifikasi,
                    'kunci' => $kunci,
                    'rapor' => $rapor,
                    'koreksi' => $kunci === 'koreksi' ? 'Matematika · salah input TP3' : null,
                    'terkunci_sejak' => $kunci === 'terkunci' ? '26 Okt 2026' : null,
                ];
            }
        }

        return collect($baris);
    }

    /** Status periode turunan per kelas (desain 5.01). */
    public static function statusPeriode(array $k): string
    {
        return match (true) {
            $k['kunci'] === 'terkunci' => 'terkunci',
            $k['kunci'] === 'koreksi' => 'koreksi',
            $k['disetujui'] === 16 => 'siap',
            $k['verifikasi'] === 'berjalan' => 'menunggu',
            default => 'terbuka',
        };
    }

    /** TODO API: GET /api/kurikulum/periode?semester= */
    public static function periode(): array
    {
        return [
            'sts' => [
                'judul' => 'Sumatif Tengah Semester (STS)', 'singkat' => 'STS Ganjil',
                'buka' => '2026-09-21', 'tutup' => '2026-10-23', 'rentang' => '21 September – 23 Oktober 2026',
                'status' => 'dibuka', 'sisa_hari' => 11, 'progres' => 66, 'perpanjangan' => false,
            ],
            'sas' => [
                'judul' => 'Sumatif Akhir Semester (SAS)', 'singkat' => 'SAS Ganjil',
                'buka' => '2026-11-16', 'tutup' => '2026-12-18', 'rentang' => '16 November – 18 Desember 2026',
                'status' => 'terjadwal', 'dibuka_dalam' => 35, 'bulan' => 'NOV', 'tanggal' => 16, 'perpanjangan' => false,
            ],
        ];
    }

    /** Mapel + guru pengampu kelas untuk modal Buka Koreksi (6.04). */
    public static function mapelKelas(): array
    {
        return array_map(fn ($m) => ['mapel' => $m[0], 'guru' => preg_replace('/,.*$/', '', $m[2])], self::MAPEL);
    }

    // ------------------------------------------------------------------ KKTP (5.03)

    /** TODO API: GET /api/kurikulum/kktp?mapel=&rombel= */
    public static function kktp(): array
    {
        $tp = [
            ['TP1', 'Menjelaskan sifat-sifat bilangan berpangkat dan bentuk akar', 'sts'],
            ['TP2', 'Menerapkan barisan dan deret aritmetika dalam masalah kontekstual', 'sts'],
            ['TP3', 'Menyelesaikan sistem persamaan linear tiga variabel', 'sts'],
            ['TP4', 'Menganalisis pertidaksamaan linear dua variabel', 'sts'],
            ['TP5', 'Menginterpretasi karakteristik fungsi kuadrat', 'semester'],
            ['TP6', 'Menyelesaikan masalah dengan pemodelan fungsi kuadrat', 'sas'],
        ];

        return [
            'tahun_ajaran' => ['2026/2027', '2025/2026'],
            'mapel' => ['Matematika', 'Bahasa Indonesia', 'Bahasa Inggris', 'Fisika'],
            'rombel' => ['X-A', 'X-B', 'X-C', 'X-D'],
            'guru' => 'Siti Rahmawati, S.Pd.',
            'interval' => [60, 75, 90],
            'tp' => array_map(fn ($t) => ['kode' => $t[0], 'deskripsi' => $t[1], 'periode' => $t[2], 'kktp' => 75], $tp),
            'templat' => [
                'terbaik' => 'Menunjukkan penguasaan yang sangat baik dalam [TP Tertinggi].',
                'penguatan' => 'Perlu penguatan dalam [TP di Bawah KKTP].',
            ],
            // Siswa contoh untuk pratinjau deskripsi. TODO API: GET /api/rombel/{id}/nilai?contoh=1
            'contoh' => [
                ['nama' => 'Dimas Saputra', 'kelas' => 'X-B', 'nilai' => [88, 92, 70, 85, 79, 81]],
                ['nama' => 'Adinda Putri', 'kelas' => 'X-B', 'nilai' => [80, 74, 82, 90, 71, 72]],
                ['nama' => 'Citra Lestari', 'kelas' => 'X-B', 'nilai' => [76, 71, 72, 83, 70, 72]],
                ['nama' => 'Gita Maharani', 'kelas' => 'X-B', 'nilai' => [87, 74, 76, 83, 74, 87]],
            ],
            'tersimpan' => '09.40',
        ];
    }

    // ------------------------------------------------------------------ Rekap DKN (6.01)

    /** Nilai per siswa satu kelas (tab "Per siswa"). TODO API: GET /api/kurikulum/dkn?kelas=&periode= */
    public static function nilaiSiswa(string $kelas, string $periode): array
    {
        $anggota = Operator::siswa()->where('kelas', $kelas)->values();
        $geser = $periode === 'semester' ? 3 : 0;

        return $anggota->map(function ($s, $i) use ($geser) {
            $nilai = [];
            foreach (array_slice(self::MAPEL, 0, 9) as $j => $m) {
                $nilai[] = 70 + (($i * 7 + $j * 11 + $geser) % 26);
            }

            return ['nis' => $s['nis'], 'nama' => $s['nama'], 'nilai' => $nilai, 'rata' => round(array_sum($nilai) / count($nilai), 1)];
        })->all();
    }
}
