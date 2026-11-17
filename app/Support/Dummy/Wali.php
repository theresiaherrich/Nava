<?php

namespace App\Support\Dummy;

/**
 * Data dummy Wali Kelas X-B (desain Wali 2.01–5.07): paket nilai per mapel, riwayat verifikasi,
 * pelengkap rapor, dan siswa kelas.
 * Status paket: menunggu | dikirim_ulang | disetujui | revisi | sedang_diisi | belum_tp | terkunci.
 */
class Wali
{
    public const KELAS = 'X-B';

    /** [mapel, guru, jumlah TP, status, dikirim, keterangan] */
    private const PAKET = [
        ['Matematika', 'Siti Rahmawati, S.Pd.', 6, 'menunggu', '12 Okt 2026, 10.15', null],
        ['Bahasa Inggris', 'Agus Prasetyo, S.Pd.', 6, 'menunggu', '12 Okt 2026, 08.30', null],
        ['Pendidikan Pancasila', 'Hadi Santoso, S.Pd.', 4, 'menunggu', '11 Okt 2026, 14.05', null],
        ['Kimia', 'Bambang Sutrisno, S.Pd.', 5, 'dikirim_ulang', '13 Okt 2026, 16.45', null],
        ['Bahasa Indonesia', 'Dra. Endang Lestari', 5, 'disetujui', '10 Okt 2026, 09.00', null],
        ['Bahasa Mandarin', 'Lin Mei, S.Pd.', 6, 'disetujui', '9 Okt 2026, 14.20', 'Mandarin 1'],
        ['Fisika', 'Rudi Hartono, M.Pd.', 5, 'disetujui', '9 Okt 2026, 11.10', null],
        ['Sejarah', 'Ika Puspitasari, S.Pd.', 4, 'disetujui', '8 Okt 2026, 13.30', null],
        ['Pend. Agama Islam dan Budi Pekerti', 'Ahmad Fauzi, S.Ag.', 5, 'disetujui', '7 Okt 2026, 10.00', 'rombel campuran'],
        ['Geografi', 'Wahyu Nugroho, S.Pd.', 4, 'disetujui', '8 Okt 2026, 09.45', null],
        ['Ekonomi', 'Ratna Dewi, S.E., M.Pd.', 4, 'disetujui', '9 Okt 2026, 10.20', null],
        ['PJOK', 'Yusuf Hidayat, S.Pd.', 4, 'disetujui', '10 Okt 2026, 15.00', null],
        ['Pend. Agama Kristen dan Budi Pekerti', 'Maria Simanjuntak, S.Th.', 5, 'disetujui', '7 Okt 2026, 11.30', 'rombel campuran'],
        ['Biologi', 'Nur Aini, S.Si.', 6, 'sedang_diisi', null, null],
        ['Bahasa Jepang', 'Dewi Anggraini, S.Pd.', 6, 'sedang_diisi', null, 'Jepang 1'],
        ['Informatika', 'Dimas Aditya, S.Kom.', 0, 'belum_tp', null, null],
    ];

    /** Mapel rombel campuran => [offset, jumlah] siswa X-B yang mengikutinya. */
    private const IRISAN = [
        'Pend. Agama Islam dan Budi Pekerti' => [0, 28],
        'Pend. Agama Kristen dan Budi Pekerti' => [28, 4],
        'Bahasa Mandarin' => [0, 14],
        'Bahasa Jepang' => [14, 14],
    ];

    /** Nama pendek kolom DKN, urut sesuai desain 4.01. */
    private const SINGKAT = [
        'Pend. Agama Islam dan Budi Pekerti' => 'PAI', 'Pendidikan Pancasila' => 'Pancasila', 'Bahasa Indonesia' => 'B. Indonesia',
        'Matematika' => 'Matematika', 'Bahasa Inggris' => 'B. Inggris', 'Fisika' => 'Fisika', 'Biologi' => 'Biologi', 'Kimia' => 'Kimia',
        'Sejarah' => 'Sejarah', 'Informatika' => 'Informatika', 'Geografi' => 'Geografi', 'Ekonomi' => 'Ekonomi', 'PJOK' => 'PJOK',
        'Pend. Agama Kristen dan Budi Pekerti' => 'PAK', 'Bahasa Mandarin' => 'Mandarin', 'Bahasa Jepang' => 'Jepang',
    ];

    /**
     * Rekap DKN kelas (desain Wali 4.01–4.02): kelengkapan per mapel dan nilai rapor per siswa.
     * Nilai yang belum diisi/belum dikirim bernilai null.
     * TODO API: GET /api/wali/rekap?periode=sts|semester
     */
    public static function dkn(): array
    {
        $paket = collect(self::paket())->sortBy(fn ($p) => array_search($p['mapel'], array_keys(self::SINGKAT), true))->values();
        $rapor = fn (array $n) => $n ? (int) round(array_sum($n) / count($n)) : null;

        $mapel = $paket->map(function ($p) use ($rapor) {
            $nilai = array_filter(array_map(fn ($b) => $rapor($b['nilai']), $p['nilai']), fn ($v) => $v !== null);
            $terkirim = ! in_array($p['status'], ['sedang_diisi', 'belum_tp'], true);
            $ket = match (true) {
                str_starts_with($p['mapel'], 'Pend. Agama') => 'Agama · rombel campuran',
                $p['campuran'] => 'Bahasa Asing · '.$p['keterangan'],
                default => 'Wajib',
            };

            return [
                'id' => $p['id'], 'mapel' => $p['mapel'], 'singkat' => self::SINGKAT[$p['mapel']], 'keterangan' => $ket,
                'guru' => $p['guru'], 'status' => $p['status'],
                'terisi' => count($nilai), 'peserta' => count($p['nilai']),
                'rata' => $terkirim && $nilai ? round(array_sum($nilai) / count($nilai), 1) : null,
                'diperbarui' => $p['dikirim'] ? preg_replace('/ \d{4},.*$/', '', $p['dikirim']) : ($p['tp'] ? '12 Okt' : null),
            ];
        })->all();

        $siswa = array_map(function ($s, $i) use ($paket, $rapor) {
            $nilai = [];
            foreach ($paket as $p) {
                $b = collect($p['nilai'])->firstWhere('nisn', $s['nisn']);
                $nilai[$p['id']] = $b ? $rapor($b['nilai']) : null;
            }
            $ada = array_filter($nilai, fn ($v) => $v !== null);

            return ['nisn' => $s['nisn'], 'nama' => $s['nama'], 'nilai' => $nilai, 'rata' => $ada ? round(array_sum($ada) / count($ada), 1) : null];
        }, self::siswa(), array_keys(self::siswa()));

        return ['mapel' => $mapel, 'siswa' => $siswa];
    }

    /** Siswa kelas X-B (urutan & nilai Matematika sesuai desain). */
    public static function siswa(): array
    {
        $rombel = collect(Guru::rombel())->firstWhere('id', 'mtk-x-b');

        return array_map(fn ($p) => ['nisn' => $p['nisn'], 'nama' => $p['nama']], Guru::peserta($rombel));
    }

    /** TODO API: GET /api/wali/paket?periode= */
    public static function paket(string $state = 'normal'): array
    {
        $siswa = self::siswa();
        $mtk = Guru::peserta(collect(Guru::rombel())->firstWhere('id', 'mtk-x-b'));

        return array_map(function ($p, $idx) use ($siswa, $mtk, $state) {
            [$mapel, $guru, $tp, $status, $dikirim, $ket] = $p;
            // Rombel campuran: siswa X-B terbagi per agama (PAI 28, PAK 4) dan per bahasa asing (Mandarin 14, Jepang 14).
            $campuran = isset(self::IRISAN[$mapel]);
            $peserta = $campuran ? array_slice($siswa, ...self::IRISAN[$mapel]) : $siswa;

            if ($state === 'kosong') {
                $status = $tp ? 'sedang_diisi' : 'belum_tp';
            } elseif ($state === 'terkunci' && $tp) {
                $status = 'terkunci';
            }

            $nilai = [];
            foreach ($peserta as $i => $s) {
                if ($mapel === 'Matematika') {
                    $v = $mtk[$i]['nilai'];
                    $v[5] = $v[5] === null ? 82 : ($v[5] > 100 ? 88 : $v[5]);
                    if ($s['nama'] === 'Dimas Saputra') {
                        $v[2] = 8; // contoh nilai di luar rentang wajar (desain 2.02)
                    }
                } else {
                    $v = array_map(fn ($j) => 68 + (($i * 7 + $j * 9 + $idx * 5) % 25), range(0, max(0, $tp - 1)));
                }
                // Mapel yang masih diisi guru: sebagian siswa belum punya nilai.
                $belumDiisi = $status === 'sedang_diisi' && $i % 6 === 5;
                $nilai[] = ['nisn' => $s['nisn'], 'nama' => $s['nama'], 'kelas' => 'X-B', 'nilai' => $tp && ! $belumDiisi ? $v : []];
            }

            return [
                'id' => 'p-'.($idx + 1),
                'mapel' => $mapel, 'guru' => $guru, 'tp' => $tp, 'status' => $status,
                'dikirim' => in_array($status, ['sedang_diisi', 'belum_tp'], true) ? null : $dikirim,
                'keterangan' => $ket, 'campuran' => $campuran,
                'jumlah_kelas_asal' => $campuran ? 28 : count($peserta),
                'nilai' => $nilai,
                'riwayat' => self::riwayat($mapel, $guru, $status),
            ];
        }, self::PAKET, array_keys(self::PAKET));
    }

    /** Catatan wali kelas contoh (indeks siswa => catatan). Siswa lain tanpa catatan memakai templat berputar. */
    private const CATATAN = [
        0 => 'Ananda aktif berdiskusi dan bertanggung jawab dalam tugas kelompok.',
        1 => 'Ananda menunjukkan kemajuan di Matematika. Tingkatkan ketelitian dalam mengerjakan soal.',
        2 => 'Ananda disiplin dan santun. Terus kembangkan bakat yang dimiliki.',
        3 => 'Ananda bersemangat dalam olahraga. Tingkatkan kehadiran di kelas.',
        4 => 'Ananda rajin dan teliti. Pertahankan prestasi belajarnya.',
        5 => 'Ananda ramah dan suka membantu teman. Perhatikan kehadiran.',
        6 => 'Ananda percaya diri dan aktif dalam kegiatan sekolah.',
        7 => 'Ananda mampu bekerja sama dengan baik. Tingkatkan kehadiran tepat waktu.',
    ];

    /** Siswa yang catatannya belum diisi (desain 3.01: 6 siswa). */
    private const BELUM_CATATAN = [3, 5, 12, 19, 24, 29];

    /**
     * Pelengkap rapor per siswa (desain Wali 3.01–3.03).
     * Kehadiran berasal dari Presensi (A1) dan ekstrakurikuler dari Kesiswaan: hanya-baca di halaman ini.
     * State: normal | belum-a1 (rekap kehadiran belum ada) | terkunci.
     * TODO API: GET /api/wali/pelengkap-rapor?periode=sts|semester
     */
    public static function pelengkap(string $state = 'normal'): array
    {
        $ekskul = [[['Pramuka', 'SB']], [['Basket', 'B']], [['Pramuka', 'SB'], ['Basket', 'B']], [['Futsal', 'B']], [['Pramuka', 'SB']], [], [['English Club', 'SB']], [['Basket', 'B']]];
        $kehadiran = [[0, 1, 0], [2, 0, 0], [0, 1, 0], [1, 2, 1], [0, 0, 0], [3, 1, 0], [0, 0, 0], [0, 1, 0]];
        $umum = ['Ananda tekun dan bertanggung jawab. Pertahankan semangat belajarnya.', 'Ananda santun dan aktif bertanya di kelas.', 'Ananda mulai menunjukkan kemajuan. Tingkatkan kedisiplinan.'];

        return array_map(function ($s, $i) use ($ekskul, $kehadiran, $umum, $state) {
            $kosong = $state !== 'terkunci' && in_array($i, self::BELUM_CATATAN, true);

            return [
                'nis' => (string) (2526001 + $i),
                'nisn' => $s['nisn'],
                'nama' => $s['nama'],
                'kehadiran' => $state === 'belum-a1' ? null : array_combine(['sakit', 'izin', 'alpa'], $kehadiran[$i] ?? [$i % 3, ($i + 1) % 2, 0]),
                'ekskul' => $i === 21 ? [] : ($ekskul[$i] ?? [[['Pramuka', 'Paskibra', 'PMR', 'Futsal', 'Paduan Suara'][$i % 5], ['SB', 'B', 'B', 'SB', 'C'][$i % 5]]]),
                'catatan' => $kosong ? '' : (self::CATATAN[$i] ?? $umum[$i % 3]),
            ];
        }, self::siswa(), array_keys(self::siswa()));
    }

    private static function riwayat(string $mapel, string $guru, string $status): array
    {
        $g = preg_replace('/,.*$/', '', $guru);
        if ($mapel === 'Kimia') {
            $r = [
                ['jenis' => 'dikirim_ulang', 'judul' => 'Dikirim ulang', 'oleh' => $g, 'waktu' => '13 Okt 2026, 16.45'],
                ['jenis' => 'revisi', 'judul' => 'Dikembalikan untuk revisi', 'oleh' => 'Siti Rahmawati (Wali Kelas)', 'waktu' => '13 Okt 2026, 13.20', 'catatan' => 'Nilai TP2 tiga siswa masih kosong.'],
                ['jenis' => 'dikirim', 'judul' => 'Dikirim', 'oleh' => $g, 'waktu' => '11 Okt 2026, 10.15'],
            ];
            if ($status !== 'dikirim_ulang') {
                array_unshift($r, ['jenis' => 'disetujui', 'judul' => 'Disetujui', 'oleh' => 'Siti Rahmawati (Wali Kelas)', 'waktu' => '14 Okt 2026, 09.10']);
            }

            return $r;
        }
        if (in_array($status, ['sedang_diisi', 'belum_tp'], true)) {
            return [];
        }
        $r = [['jenis' => 'dikirim', 'judul' => 'Dikirim', 'oleh' => $g, 'waktu' => '10 Okt 2026, 09.00']];
        if (in_array($status, ['disetujui', 'terkunci'], true)) {
            array_unshift($r, ['jenis' => 'disetujui', 'judul' => 'Disetujui', 'oleh' => 'Siti Rahmawati (Wali Kelas)', 'waktu' => '11 Okt 2026, 08.00']);
        }
        if ($status === 'terkunci') {
            array_unshift($r, ['jenis' => 'terkunci', 'judul' => 'Dikunci Kurikulum', 'oleh' => 'Dra. Wahyuni, M.Pd.', 'waktu' => '26 Okt 2026, 14.05']);
        }

        return $r;
    }
}
