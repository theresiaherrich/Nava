<?php

namespace App\Support\Dummy;

use Illuminate\Support\Str;

/**
 * Data dummy Rombel & Pembagian Mengajar (desain Kurikulum 4.05–4.09).
 * Total 312 rombel (104 per tingkat), 4 tanpa pengampu, 18 rombel campuran.
 */
class Rombel
{
    private const KELAS = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];

    private const MAPEL_REGULER = [
        'Matematika' => 'Siti Rahmawati, S.Pd.',
        'Fisika' => 'Rudi Hartono, M.Pd.',
        'Kimia' => 'Bambang Sutrisno, S.Pd.',
        'Biologi' => 'Nur Aini, S.Si.',
        'Informatika' => 'Wahyu Setiawan, S.Pd.',
        'Bahasa Indonesia' => 'Dra. Endang Lestari',
        'Bahasa Inggris' => 'Agus Prasetyo, S.Pd.',
        'Sejarah' => 'Hadi Santoso, S.Pd.',
        'PJOK' => 'Ahmad Fauzi, S.Ag.',
        'Seni Budaya' => 'Rina Marlina, S.Sos.',
    ];

    /** TODO API: GET /api/kurikulum/rombel?semester=&tingkat=&mapel=&jenis=&q= */
    public static function semua(): array
    {
        $rombel = [];

        foreach (['X', 'XI', 'XII'] as $tingkat) {
            $rombel = [...$rombel, ...self::campuran($tingkat)];
        }

        foreach (['X', 'XI', 'XII'] as $tingkat) {
            $jumlahCampuran = count(array_filter($rombel, fn ($r) => $r['tingkat'] === $tingkat));
            $reguler = [];
            foreach (self::MAPEL_REGULER as $mapel => $guru) {
                foreach (self::KELAS as $k) {
                    $reguler[] = self::baris("$mapel $tingkat-$k", $mapel, $tingkat, 'reguler', ["$tingkat-$k"], 32, $guru);
                }
            }
            $rombel = [...$rombel, ...array_slice($reguler, 0, 104 - $jumlahCampuran)];
        }

        // Rombel tanpa pengampu: Informatika X-C, Kimia XI-D, Fisika XII-H (+ Jerman 1 di campuran).
        foreach ($rombel as &$r) {
            if (in_array($r['nama'], ['Informatika X-C', 'Kimia XI-D', 'Fisika XII-H'], true)) {
                $r['guru'] = null;
            }
        }
        unset($r);

        // Urutan Kelas X mengikuti desain 4.05.
        $depan = ['Matematika X-A', 'Matematika X-B', 'Mandarin 1', 'Mandarin 2', 'Jerman 1', 'Pend. Agama Kristen X', 'Informatika X-C', 'Fisika X-B'];
        usort($rombel, function ($a, $b) use ($depan) {
            $ia = array_search($a['nama'], $depan, true);
            $ib = array_search($b['nama'], $depan, true);

            return [$ia === false ? 99 : $ia] <=> [$ib === false ? 99 : $ib];
        });

        return $rombel;
    }

    /** Guru calon pengampu untuk dropdown "Pilih guru" (desain 4.06). TODO API: GET /api/master/guru?mapel= */
    public static function calonPengampu(): array
    {
        return [
            ['id_guru' => 'g-maria', 'nama' => 'Maria Hartati, S.Pd.', 'status' => 'GTT', 'beban' => 4, 'penuh' => false],
            ['id_guru' => 'g-dewi', 'nama' => 'Dewi Anggraini, S.Pd.', 'status' => 'ASN', 'beban' => 2, 'penuh' => false],
            ['id_guru' => 'g-fransiska', 'nama' => 'Fransiska Wulan, M.Pd.', 'status' => 'ASN', 'beban' => 5, 'penuh' => false],
            ['id_guru' => 'g-rudi', 'nama' => 'Rudi Hartono, M.Pd.', 'status' => 'ASN', 'beban' => 8, 'penuh' => true],
            ['id_guru' => 'g-lin', 'nama' => 'Lin Mei, S.Pd.', 'status' => 'GTT', 'beban' => 3, 'penuh' => false],
        ];
    }

    /** Daftar mapel untuk modal Buat Rombel. */
    public static function daftarMapel(): array
    {
        return [...array_keys(self::MAPEL_REGULER), 'Bahasa Mandarin', 'Bahasa Jerman', 'Bahasa Jepang', 'Bahasa Arab', 'P. Agama Kristen', 'P. Agama Katolik'];
    }

    public static function daftarKelas(): array
    {
        $kelas = [];
        foreach (['X', 'XI', 'XII'] as $t) {
            foreach (self::KELAS as $k) {
                $kelas[] = "$t-$k";
            }
        }

        return $kelas;
    }

    /** TODO API: GET /api/kurikulum/rombel/{id} (beserta peserta) */
    public static function detail(string $id): ?array
    {
        $rombel = collect(self::semua())->firstWhere('id_rombel', $id);
        if (! $rombel) {
            return null;
        }

        $peserta = $id === 'mandarin-1' ? self::pesertaMandarin1() : self::pesertaUmum($rombel);

        $perKelas = collect($peserta)->groupBy('kelas_asal');
        $wali = WaliKelas::waliPerKelas();

        return $rombel + [
            'guru_status' => $id === 'mandarin-1' ? 'GTT' : 'ASN',
            'jumlah_tp' => 6,
            'status_sts' => 'sedang_diisi',
            'semester' => '2026/2027 Ganjil',
            'kelas_asal_detail' => collect($rombel['kelas_asal'])->map(fn ($k) => [
                'kelas' => $k,
                'jumlah' => $perKelas->get($k, collect())->count(),
                'wali' => $k === 'X-A' ? 'Andini P.' : preg_replace('/,.*$/', '', $wali[$k] ?? '—'),
            ])->all(),
            'peserta' => $peserta,
        ];
    }

    /** Siswa kelas asal yang bisa ditambahkan (desain 4.09). TODO API: GET /api/kurikulum/rombel/{id}/calon-peserta */
    public static function calonPeserta(array $rombel): array
    {
        $terdaftar = collect($rombel['peserta'])->pluck('nis')->all();

        $calon = [
            ['nis' => '2526017', 'nama' => 'Ahmad Rizky', 'kelas_asal' => 'X-A'],
            ['nis' => '2526021', 'nama' => 'Bima Sakti', 'kelas_asal' => 'X-A'],
            ['nis' => '2526026', 'nama' => 'Dafa Ramadhan', 'kelas_asal' => 'X-A'],
            ['nis' => '2526027', 'nama' => 'Elisa Putri', 'kelas_asal' => 'X-A'],
            ['nis' => '2526030', 'nama' => 'Gilang Pratama', 'kelas_asal' => 'X-A', 'rombel_lain' => 'Mandarin 2'],
            ['nis' => '2526032', 'nama' => 'Hana Safira', 'kelas_asal' => 'X-A'],
            ['nis' => '2526033', 'nama' => 'Irfan Hakim', 'kelas_asal' => 'X-A'],
            ['nis' => '2526004', 'nama' => 'Dimas Saputra', 'kelas_asal' => 'X-B'],
            ['nis' => '2526005', 'nama' => 'Eka Wulandari', 'kelas_asal' => 'X-B'],
            ['nis' => '2526034', 'nama' => 'Joko Susilo', 'kelas_asal' => 'X-B'],
            ['nis' => '2526035', 'nama' => 'Lukman Hakim', 'kelas_asal' => 'X-B', 'rombel_lain' => 'Mandarin 2'],
        ];

        return array_map(fn ($s) => $s + [
            'terdaftar' => in_array($s['nis'], $terdaftar, true),
            'rombel_lain' => $s['rombel_lain'] ?? null,
        ], array_values(array_filter($calon, fn ($s) => in_array($s['kelas_asal'], $rombel['kelas_asal'], true))));
    }

    private static function campuran(string $t): array
    {
        if ($t === 'X') {
            return [
                self::baris('Mandarin 1', 'Bahasa Mandarin', 'X', 'campuran', ['X-A', 'X-B'], 28, 'Lin Mei, S.Pd.'),
                self::baris('Mandarin 2', 'Bahasa Mandarin', 'X', 'campuran', ['X-C', 'X-D'], 26, 'Lin Mei, S.Pd.'),
                self::baris('Jerman 1', 'Bahasa Jerman', 'X', 'campuran', ['X-A', 'X-E'], 24, null),
                self::baris('Pend. Agama Kristen X', 'P. Agama Kristen', 'X', 'campuran', array_map(fn ($k) => "X-$k", self::KELAS), 22, 'Yohanes Setiawan, S.Th.'),
                self::baris('Jepang 1', 'Bahasa Jepang', 'X', 'campuran', ['X-F', 'X-G'], 25, 'Dewi Anggraini, S.Pd.'),
                self::baris('Arab 1', 'Bahasa Arab', 'X', 'campuran', ['X-H', 'X-I'], 20, 'Ahmad Fauzi, S.Ag.'),
            ];
        }

        return [
            self::baris("Mandarin $t-1", 'Bahasa Mandarin', $t, 'campuran', ["$t-A", "$t-B"], 27, 'Lin Mei, S.Pd.'),
            self::baris("Mandarin $t-2", 'Bahasa Mandarin', $t, 'campuran', ["$t-C", "$t-D"], 25, 'Lin Mei, S.Pd.'),
            self::baris("Jerman $t-1", 'Bahasa Jerman', $t, 'campuran', ["$t-A", "$t-E"], 23, 'Maria Hartati, S.Pd.'),
            self::baris("Pend. Agama Kristen $t", 'P. Agama Kristen', $t, 'campuran', array_map(fn ($k) => "$t-$k", self::KELAS), 21, 'Yohanes Setiawan, S.Th.'),
            self::baris("Jepang $t-1", 'Bahasa Jepang', $t, 'campuran', ["$t-F", "$t-G"], 24, 'Dewi Anggraini, S.Pd.'),
            self::baris("Arab $t-1", 'Bahasa Arab', $t, 'campuran', ["$t-H", "$t-I"], 19, 'Ahmad Fauzi, S.Ag.'),
        ];
    }

    private static function baris(string $nama, string $mapel, string $tingkat, string $jenis, array $kelasAsal, int $peserta, ?string $guru): array
    {
        return [
            'id_rombel' => Str::slug($nama),
            'nama' => $nama,
            'mapel' => $mapel,
            'tingkat' => $tingkat,
            'jenis' => $jenis,
            'kelas_asal' => $kelasAsal,
            'jumlah_peserta' => $peserta,
            'guru' => $guru,
        ];
    }

    private static function pesertaMandarin1(): array
    {
        $baris = [
            ['2526017', 'Ahmad Rizky', 'X-A'], ['2526001', 'Adinda Putri', 'X-B'], ['2526021', 'Bima Sakti', 'X-A'],
            ['2526003', 'Citra Lestari', 'X-B'], ['2526024', 'Clara Wijaya', 'X-A'], ['2526007', 'Gita Maharani', 'X-B'],
            ['2526029', 'Farah Nadia', 'X-A'], ['2526010', 'Indah Permata', 'X-B'], ['2526031', 'Kevin Santoso', 'X-A'],
            ['2526012', 'Kurnia Sari', 'X-B'], ['2526036', 'Laras Ayu', 'X-A'], ['2526013', 'Maya Anggraini', 'X-B'],
            ['2526037', 'Nadia Rahma', 'X-A'], ['2526014', 'Nanda Pratiwi', 'X-B'], ['2526038', 'Oki Firmansyah', 'X-A'],
            ['2526015', 'Putra Wijaya', 'X-B'], ['2526039', 'Rani Oktavia', 'X-A'], ['2526016', 'Reza Mahendra', 'X-B'],
            ['2526040', 'Salsa Bila', 'X-A'], ['2526018', 'Sinta Dewi', 'X-B'], ['2526041', 'Taufik Hidayat', 'X-A'],
            ['2526019', 'Tiara Kusuma', 'X-B'], ['2526042', 'Umar Said', 'X-A'], ['2526020', 'Vina Amalia', 'X-B'],
            ['2526043', 'Wulan Sari', 'X-A'], ['2526022', 'Yoga Pratama', 'X-B'], ['2526044', 'Zahra Aulia', 'X-A'],
            ['2526023', 'Zaki Ramadhan', 'X-B'],
        ];

        return array_map(fn ($s) => ['nis' => $s[0], 'nama' => $s[1], 'kelas_asal' => $s[2]], $baris);
    }

    private static function pesertaUmum(array $rombel): array
    {
        $nama = ['Adinda Putri', 'Bagas Pratama', 'Citra Lestari', 'Dimas Saputra', 'Eka Wulandari', 'Fajar Nugroho', 'Gita Maharani', 'Hendra Wijaya', 'Indah Permata', 'Joko Susilo', 'Kurnia Sari', 'Lukman Hakim', 'Maya Anggraini', 'Nanda Pratiwi', 'Oki Firmansyah', 'Putra Wijaya'];
        $peserta = [];
        for ($i = 0; $i < $rombel['jumlah_peserta']; $i++) {
            $peserta[] = [
                'nis' => (string) (2526100 + $i),
                'nama' => $nama[$i % count($nama)].($i >= count($nama) ? ' '.chr(65 + intdiv($i, count($nama)) - 1) : ''),
                'kelas_asal' => $rombel['kelas_asal'][$i % count($rombel['kelas_asal'])],
            ];
        }

        return $peserta;
    }
}
