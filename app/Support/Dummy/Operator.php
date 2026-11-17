<?php

namespace App\Support\Dummy;

use Illuminate\Support\Collection;

/**
 * Data dummy modul Operator (desain Operator 2.01–2.10).
 *
 * Baris yang tampil di desain ditulis persis; sisanya dibangkitkan deterministik
 * agar total sama dengan desain: 2.010 akun, 960 siswa (30 kelas × 32),
 * 86 guru (62 ASN · 24 GTT), 904 orang tua terhubung, 56 siswa belum terhubung.
 */
class Operator
{
    public const KELAS = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];

    public const TINGKAT = ['X', 'XI', 'XII'];

    public const AGAMA = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'];

    public const PERAN = ['Admin Kurikulum', 'Admin Kesiswaan', 'Operator', 'Guru', 'Wali Kelas', 'Guru BK', 'Siswa', 'Orang Tua'];

    private const DEPAN = ['Eko', 'Elisa', 'Erlangga', 'Fadil', 'Farah', 'Fikri', 'Galang', 'Gilang', 'Hana', 'Hafiz', 'Ilham', 'Intan', 'Jihan', 'Kevin', 'Kirana', 'Laras', 'Lukman', 'Maya', 'Nadia', 'Naufal', 'Oki', 'Putri', 'Qori', 'Rafa', 'Rani', 'Reza', 'Salsa', 'Sinta', 'Taufik', 'Tiara', 'Umar', 'Vina', 'Wulan', 'Yoga', 'Zahra', 'Zaki'];

    private const BELAKANG = ['Pratama', 'Saputra', 'Lestari', 'Wijaya', 'Ramadhan', 'Kusuma', 'Permata', 'Hidayat', 'Nugroho', 'Maharani', 'Santoso', 'Anggraini', 'Firmansyah', 'Wulandari', 'Setiawan', 'Rahmawati', 'Utami', 'Purnama'];

    private static array $cache = [];

    // ------------------------------------------------------------------ Dashboard

    /** TODO API: GET /api/operator/dashboard */
    public static function dashboard(): array
    {
        $siswa = self::siswa();
        $guru = self::guru();
        $belumOrtu = $siswa->where('orang_tua', 'belum')->count();
        $guruTanpaAkun = $guru->where('akun', 'belum')->count();

        return [
            'sapaan' => 'Pak Andi',
            'hari_ini' => 'Senin, 12 Oktober 2026',
            'statistik' => [
                'akun' => self::akun()->count(),
                'siswa' => $siswa->count(),
                'kelas' => 30,
                'guru' => $guru->count(),
                'asn' => $guru->where('status', 'ASN')->count(),
                'gtt' => $guru->where('status', 'GTT')->count(),
                'ortu_terhubung' => $siswa->count() - $belumOrtu,
                'ortu_belum' => $belumOrtu,
            ],
            'tindak_lanjut' => [
                ['ikon' => 'link', 'tone' => 'warning', 'teks' => "{$belumOrtu} siswa belum terhubung ke akun orang tua", 'aksi' => 'Hubungkan', 'route' => ['relasi-orang-tua.index', ['modal' => 'hubungkan']]],
                ['ikon' => 'key', 'tone' => 'warning', 'teks' => "{$guruTanpaAkun} guru belum memiliki akun", 'aksi' => 'Buat akun', 'route' => ['akun-peran.index', ['modal' => 'akun']]],
                ['ikon' => 'alert-circle', 'tone' => 'danger', 'teks' => '2 baris impor gagal (data_siswa_2026.xlsx)', 'aksi' => 'Perbaiki', 'route' => ['data-siswa.index', ['modal' => 'impor']]],
            ],
            // TODO API: GET /api/log-aktivitas?limit=5
            'aktivitas' => [
                ['ikon' => 'upload', 'tone' => 'success', 'judul' => 'Impor 960 siswa', 'ket' => 'data_siswa_2026.xlsx · 958 berhasil, 2 gagal', 'waktu' => '1 Sep 2026, 09.14'],
                ['ikon' => 'key', 'tone' => 'info', 'judul' => 'Akun Lin Mei (GTT) dibuat', 'ket' => 'Peran: Guru', 'waktu' => '2 Sep 2026, 10.02'],
                ['ikon' => 'upload', 'tone' => 'info', 'judul' => 'Impor 86 guru', 'ket' => 'data_guru_2026.xlsx', 'waktu' => '2 Sep 2026, 08.40'],
                ['ikon' => 'link', 'tone' => 'violet', 'judul' => '12 orang tua dihubungkan', 'ket' => 'Kelas X-A – X-D', 'waktu' => '5 Sep 2026, 13.25'],
                ['ikon' => 'building', 'tone' => 'neutral', 'judul' => 'Data kelas Ganjil 2026/2027 disalin', 'ket' => '30 kelas', 'waktu' => '28 Agu 2026, 15.10'],
            ],
        ];
    }

    // ------------------------------------------------------------------ Siswa

    /** TODO API: GET /api/master/siswa?tingkat=&kelas=&agama=&q=&page= */
    public static function siswa(): Collection
    {
        return self::$cache['siswa'] ??= self::bangunSiswa();
    }

    private static function bangunSiswa(): Collection
    {
        $s = fn ($nis, $nisn, $nama, $lp, $agama, $kelas, $ortu = 'terhubung') => compact('nis', 'nisn', 'nama', 'lp', 'agama', 'kelas', 'ortu');

        // Urutan halaman pertama Data Siswa (2.04) dan anggota X-B (2.07) sesuai desain.
        $tetap = collect([
            $s('2526001', '0081234561', 'Adinda Putri', 'P', 'Islam', 'X-B'),
            $s('2425014', '0071230214', 'Agus Salim', 'L', 'Islam', 'XI-C'),
            $s('2526017', '0081230017', 'Ahmad Rizky', 'L', 'Islam', 'X-A'),
            $s('2324008', '0061230808', 'Alya Rahmawati', 'P', 'Islam', 'XII-B'),
            $s('2526002', '0081234562', 'Bagas Pratama', 'L', 'Islam', 'X-B'),
            $s('2425031', '0071230231', 'Bella Saftri', 'P', 'Kristen', 'XI-A', 'belum'),
            $s('2526003', '0081234567', 'Citra Lestari', 'P', 'Kristen', 'X-B'),
            $s('2526009', '0081234590', 'Citra Dewi', 'P', 'Islam', 'X-B', 'belum'),
            $s('2526004', '0081234564', 'Dimas Saputra', 'L', 'Islam', 'X-B'),
            $s('2526112', '0081231112', 'Dinda Ayu Lestari', 'P', 'Hindu', 'X-D'),
            $s('2526005', '0081234565', 'Eka Wulandari', 'P', 'Islam', 'X-B'),
            $s('2526006', '0081234566', 'Fajar Nugroho', 'L', 'Katolik', 'X-B'),
            $s('2526007', '0081234568', 'Gita Maharani', 'P', 'Hindu', 'X-B'),
            $s('2526008', '0081234569', 'Hendra Wijaya', 'L', 'Islam', 'X-B'),
            $s('2526010', '0081234570', 'Indah Permata', 'P', 'Islam', 'X-B'),
            $s('2425007', '0071230007', 'Rafi Gunawan', 'L', 'Islam', 'XI-A'),
            $s('2526180', '0081230180', 'Rani Gunawati', 'P', 'Islam', 'X-F', 'belum'),
        ]);

        $isi = $tetap->countBy('kelas');
        $belumTarget = 56 - $tetap->where('ortu', 'belum')->count();
        $angkatan = ['X' => '2526', 'XI' => '2425', 'XII' => '2324'];
        $nisnAwal = ['X' => '00812', 'XI' => '00712', 'XII' => '00612'];
        $hasil = [];
        $i = 0;

        foreach (self::TINGKAT as $t) {
            foreach (self::KELAS as $k) {
                $kelas = "$t-$k";
                for ($n = $isi[$kelas] ?? 0; $n < 32; $n++, $i++) {
                    $nama = self::DEPAN[$i % count(self::DEPAN)].' '.self::BELAKANG[intdiv($i, count(self::DEPAN)) % count(self::BELAKANG)];
                    $nomor = 200 + $i;
                    $hasil[] = $s(
                        $angkatan[$t].str_pad((string) $nomor, 3, '0', STR_PAD_LEFT),
                        $nisnAwal[$t].str_pad((string) (30000 + $i), 5, '0', STR_PAD_LEFT),
                        $nama,
                        $i % 2 ? 'L' : 'P',
                        self::AGAMA[$i % 23 === 0 ? 1 + ($i % 4) : 0],
                        $kelas,
                        ($i % 17 === 5 && $belumTarget-- > 0) ? 'belum' : 'terhubung',
                    );
                }
            }
        }

        $hasil = collect($hasil)->sortBy('nama', SORT_NATURAL)->values();

        return $tetap->concat($hasil)->values()->map(fn ($r, $idx) => [
            'id_siswa' => $idx + 1,
            'nis' => $r['nis'],
            'nisn' => $r['nisn'],
            'nama' => $r['nama'],
            'jenis_kelamin' => $r['lp'],
            'agama' => $r['agama'],
            'kelas' => $r['kelas'],
            'tingkat' => explode('-', $r['kelas'])[0],
            'orang_tua' => $r['ortu'],
        ]);
    }

    /** Hasil pratinjau impor Excel (desain 2.05). TODO API: POST /api/master/siswa/impor/pratinjau */
    public static function hasilImpor(string $jenis): array
    {
        return $jenis === 'guru'
            ? ['berkas' => 'data_guru_2026.xlsx', 'baris' => 86, 'ukuran' => '24 KB', 'valid' => 85, 'masalah' => [
                ['baris' => 41, 'kolom' => 'NIP', 'nilai' => '19850312201001100', 'pesan' => 'NIP harus 18 digit'],
            ]]
            : ['berkas' => 'data_siswa_2026.xlsx', 'baris' => 960, 'ukuran' => '184 KB', 'valid' => 958, 'masalah' => [
                ['baris' => 15, 'kolom' => 'NISN', 'nilai' => '0081234567', 'pesan' => 'NISN ganda dengan baris 9'],
                ['baris' => 302, 'kolom' => 'Kelas', 'nilai' => 'X-K', 'pesan' => 'Kelas tidak ditemukan'],
            ]];
    }

    // ------------------------------------------------------------------ Guru

    /** TODO API: GET /api/master/guru?status=&mapel=&q=&page= */
    public static function guru(): Collection
    {
        return self::$cache['guru'] ??= self::bangunGuru();
    }

    private static function bangunGuru(): Collection
    {
        $g = fn ($nama, $nip, $status, $peran, $mapel, $akun = 'aktif') => compact('nama', 'nip', 'status', 'peran', 'mapel', 'akun');

        $tetap = [
            $g('Agus Prasetyo, S.Pd.', '198503122010011004', 'ASN', ['Guru'], 'B. Inggris'),
            $g('Ahmad Fauzi, S.Ag.', '197911082006041002', 'ASN', ['Guru'], 'PAI'),
            $g('Bambang Sutrisno, S.Pd.', '198107152008011007', 'ASN', ['Guru'], 'Kimia'),
            $g('Dimas Aditya, S.Kom.', null, 'GTT', ['Guru'], 'Informatika', 'belum'),
            $g('Dra. Endang Lestari', '196805201994032005', 'ASN', ['Guru', 'Wali Kelas'], 'B. Indonesia'),
            $g('Dra. Wahyuni, M.Pd.', '196904101995122003', 'ASN', ['Admin Kurikulum', 'Guru'], 'Matematika'),
            $g('Lin Mei, S.Pd.', null, 'GTT', ['Guru'], 'Mandarin'),
            $g('Nur Aini, S.Si.', '198812032014022001', 'ASN', ['Guru'], 'Biologi'),
            $g('Siti Rahmawati, S.Pd.', '198602142011012008', 'ASN', ['Guru', 'Wali Kelas'], 'Matematika'),
            $g('Yohanes Setiawan, S.Th.', null, 'GTT', ['Guru'], 'P. Agama Kristen', 'nonaktif'),
        ];

        $sudah = array_column($tetap, 'nama');
        $wali = array_values(WaliKelas::waliPerKelas());
        $nama = collect([...WaliKelas::guru(), ...$wali, 'Rudi Hartono, M.Pd.', 'Maria Hartati, S.Pd.', 'Fransiska Wulan, M.Pd.'])
            ->unique()->reject(fn ($n) => in_array($n, $sudah, true))->values();

        $mapel = ['Matematika', 'Fisika', 'Kimia', 'Biologi', 'B. Indonesia', 'B. Inggris', 'Sejarah', 'PJOK', 'Seni Budaya', 'Informatika', 'Ekonomi', 'Geografi', 'Sosiologi', 'PPKn', 'B. Jawa', 'B. Jerman', 'B. Jepang'];
        $tambahan = [];
        for ($i = 0; count($tambahan) < 76; $i++) {
            $n = $nama[$i] ?? 'Guru '.self::DEPAN[$i % count(self::DEPAN)].' '.self::BELAKANG[$i % count(self::BELAKANG)].', S.Pd.';
            $asn = $i % 7 !== 3 && count(array_filter($tambahan, fn ($x) => $x['status'] === 'ASN')) < 55;
            $tambahan[] = $g(
                $n,
                $asn ? sprintf('19%02d%02d%02d20%02d%02d%04d', 70 + $i % 20, 1 + $i % 12, 1 + $i % 28, 5 + $i % 15, 1 + $i % 12, 1001 + $i) : null,
                $asn ? 'ASN' : 'GTT',
                in_array($n, $wali, true) ? ['Guru', 'Wali Kelas'] : ['Guru'],
                $n === 'Rudi Hartono, M.Pd.' ? 'Fisika' : $mapel[$i % count($mapel)],
                in_array($i, [17, 44], true) ? 'belum' : 'aktif',
            );
        }

        return collect([...$tetap, ...collect($tambahan)->sortBy('nama', SORT_NATURAL)->all()])
            ->values()->map(fn ($r, $idx) => ['id_guru' => $idx + 1, ...$r]);
    }

    // ------------------------------------------------------------------ Akun

    /** TODO API: GET /api/akun?peran=&status=&q=&page= */
    public static function akun(): Collection
    {
        return self::$cache['akun'] ??= self::bangunAkun();
    }

    private static function bangunAkun(): Collection
    {
        $a = fn ($nama, $username, $peran, $status, $login) => compact('nama', 'username', 'peran', 'status', 'login');

        $tetap = collect([
            $a('Dra. Wahyuni, M.Pd.', 'wahyuni', ['Admin Kurikulum', 'Guru'], 'aktif', 'Hari ini, 07.12'),
            $a('Siti Rahmawati, S.Pd.', 'siti.rahmawati', ['Guru', 'Wali Kelas'], 'aktif', 'Hari ini, 06.58'),
            $a('Andi Pratama', 'andi.pratama', ['Operator'], 'aktif', 'Hari ini, 06.45'),
            $a('Agus Prasetyo, S.Pd.', 'agus.prasetyo', ['Guru'], 'aktif', 'Kemarin, 15.20'),
            $a('Lin Mei, S.Pd.', 'lin.mei', ['Guru'], 'aktif', '10 Okt 2026'),
            $a('Drs. Bambang Hartono', 'bambang.h', ['Admin Kesiswaan', 'Guru'], 'aktif', '9 Okt 2026'),
            $a('Rina Kusuma, S.Psi.', 'rina.kusuma', ['Guru BK'], 'aktif', '8 Okt 2026'),
            $a('Adinda Putri', '2526001', ['Siswa'], 'aktif', '7 Okt 2026'),
            $a('Bapak Hendra Gunawan', 'hendra.g', ['Orang Tua'], 'aktif', '5 Okt 2026'),
            $a('Yohanes Setiawan, S.Th.', 'yohanes.s', ['Guru'], 'nonaktif', '2 Sep 2026'),
        ]);
        $ada = $tetap->pluck('nama')->all();
        $login = ['3 Okt 2026', '1 Okt 2026', '29 Sep 2026', '24 Sep 2026', '18 Sep 2026', '—'];

        $guru = self::guru()->where('akun', '!=', 'belum')->reject(fn ($g) => in_array($g['nama'], $ada, true))
            ->map(fn ($g, $i) => $a($g['nama'], self::username($g['nama']), $g['peran'], 'aktif', $login[$i % 5]));
        $siswa = self::siswa()->reject(fn ($s) => in_array($s['nama'], $ada, true))
            ->map(fn ($s, $i) => $a($s['nama'], $s['nis'], ['Siswa'], 'aktif', $login[$i % 6]));
        $ortu = self::relasi()->reject(fn ($r) => in_array($r['nama'], $ada, true))
            ->map(fn ($r, $i) => $a($r['nama'], $r['username'], ['Orang Tua'], 'aktif', $login[($i + 2) % 6]));
        $staf = collect(['Sri Lestari, A.Md.', 'Joko Purnomo', 'Dewi Sartika, S.E.'])->map(fn ($n) => $a($n, self::username($n), ['Operator'], 'aktif', '30 Sep 2026'));

        $semua = $tetap->concat($guru)->concat($staf)->concat($siswa)->concat($ortu)->take(2010)->values();

        // Lengkapi sampai 2.010 dengan akun orang tua kedua (ayah/ibu) yang belum dihubungkan.
        for ($i = 0; $semua->count() < 2010; $i++) {
            $nama = ($i % 2 ? 'Bapak ' : 'Ibu ').self::DEPAN[$i % count(self::DEPAN)].' '.self::BELAKANG[($i * 7) % count(self::BELAKANG)];
            $semua->push($a($nama, self::username($nama).$i, ['Orang Tua'], 'aktif', '—'));
        }

        // 16 akun nonaktif sesuai ringkasan desain (Yohanes + 15 lainnya).
        return $semua->map(function ($r, $i) {
            if ($i >= 200 && $i % 113 === 0 && $i < 200 + 113 * 15) {
                $r['status'] = 'nonaktif';
            }

            return ['id_user' => $i + 1, ...$r];
        });
    }

    // ------------------------------------------------------------------ Relasi orang tua

    /** TODO API: GET /api/master/orang-tua?status=&hubungan=&q=&page= */
    public static function relasi(): Collection
    {
        return self::$cache['relasi'] ??= self::bangunRelasi();
    }

    private static function bangunRelasi(): Collection
    {
        $r = fn ($nama, $username, $hubungan, $anak, $status = 'terverifikasi') => compact('nama', 'username', 'hubungan', 'anak', 'status');
        $anak = fn ($nama, $kelas) => compact('nama', 'kelas');

        $tetap = collect([
            $r('Bapak Hendra Gunawan', 'hendra.g', 'Ayah', [$anak('Adinda Putri', 'X-B'), $anak('Rafi Gunawan', 'XI-A')]),
            $r('Ibu Sri Wahyuni', 'sri.wahyuni', 'Ibu', [$anak('Bagas Pratama', 'X-B')]),
            $r('Bapak Yosef Lestari', 'yosef.l', 'Ayah', [$anak('Citra Lestari', 'X-B')]),
            $r('Ibu Ratna Saputra', 'ratna.s', 'Ibu', [$anak('Dimas Saputra', 'X-B')]),
            $r('Bapak Ahmad Salim', 'ahmad.salim', 'Ayah', [$anak('Agus Salim', 'XI-C')], 'menunggu'),
            $r('Ibu Wulan Sari', 'wulan.sari', 'Ibu', [$anak('Eka Wulandari', 'X-B')], 'menunggu'),
            $r('Bapak Made Maharani', 'made.m', 'Wali', [$anak('Gita Maharani', 'X-B')]),
            $r('Ibu Dewi Rizky', 'dewi.rizky', 'Ibu', [$anak('Ahmad Rizky', 'X-A')]),
        ]);

        $sudah = $tetap->flatMap(fn ($x) => array_column($x['anak'], 'nama'))->all();
        $lainnya = self::siswa()->where('orang_tua', 'terhubung')
            ->reject(fn ($s) => in_array($s['nama'], $sudah, true))
            ->values()->take(904 - $tetap->count())
            ->map(function ($s, $i) use ($r, $anak) {
                $ayah = $i % 3 !== 1;
                $belakang = last(explode(' ', $s['nama']));
                $depan = ['Budi', 'Sri', 'Agus', 'Rina', 'Hadi', 'Wati', 'Yanto', 'Dewi', 'Joko', 'Lina'][$i % 10];
                $nama = ($ayah ? 'Bapak ' : 'Ibu ').$depan.' '.$belakang;

                return $r($nama, strtolower($depan.'.'.$belakang).($i > 9 ? $i : ''), $ayah ? 'Ayah' : 'Ibu', [$anak($s['nama'], $s['kelas'])], $i % 29 === 4 ? 'menunggu' : 'terverifikasi');
            });

        // Kedua orang tua bisa punya akun sendiri: tambah ibu untuk anak pertama bila total belum 904.
        $semua = $tetap->concat($lainnya)->values();
        if ($semua->count() < 904) {
            $anakPertama = $lainnya->first()['anak'];
            $semua->push($r('Ibu Sari '.last(explode(' ', $anakPertama[0]['nama'])), 'sari.ibu', 'Ibu', $anakPertama));
        }

        return $semua->map(fn ($x, $i) => ['id_orang_tua' => $i + 1, ...$x]);
    }

    /** Calon anak untuk modal Hubungkan (desain 2.10): siswa yang belum terhubung. */
    public static function calonAnak(): array
    {
        $calon = self::siswa()->where('orang_tua', 'belum')->values()->map(fn ($s) => [
            'nis' => $s['nis'], 'nama' => $s['nama'], 'kelas' => $s['kelas'],
        ]);

        return collect([
            ['nis' => '2526001', 'nama' => 'Adinda Putri', 'kelas' => 'X-B'],
            ['nis' => '2425007', 'nama' => 'Rafi Gunawan', 'kelas' => 'XI-A'],
        ])->concat($calon)->unique('nis')->values()->all();
    }

    // ------------------------------------------------------------------ Kelas

    /** TODO API: GET /api/master/kelas?semester=&tingkat= (beserta anggota) */
    public static function kelas(string $tingkat): array
    {
        $wali = WaliKelas::waliPerKelas();
        $anggota = self::siswa()->groupBy('kelas');

        return collect(self::KELAS)->map(fn ($k) => [
            'id_kelas' => strtolower("$tingkat-$k"),
            'kelas' => "$tingkat-$k",
            'wali' => $wali["$tingkat-$k"] ?? null,
            'anggota' => $anggota->get("$tingkat-$k", collect())->map(fn ($s) => [
                'nis' => $s['nis'], 'nama' => $s['nama'], 'jenis_kelamin' => $s['jenis_kelamin'], 'agama' => $s['agama'],
            ])->values()->all(),
        ])->all();
    }

    private static function username(string $nama): string
    {
        $bersih = strtolower(preg_replace('/,.*$|\b(Dra?|Drs|Ir|Hj?)\.\s*/u', '', $nama));
        $kata = preg_split('/\s+/', trim($bersih));

        return $kata[0].(isset($kata[1]) ? '.'.$kata[1] : '');
    }
}
