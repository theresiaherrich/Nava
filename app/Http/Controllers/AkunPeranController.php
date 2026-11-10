<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AkunPeranController extends Controller
{
    private const PER_PAGE = 10;

    // Daftar peran yang tersedia, beserta warna label
    public const PERAN = [
        'guru'             => ['label' => 'Guru',              'tone' => 'gray'],
        'wali_kelas'       => ['label' => 'Wali Kelas',        'tone' => 'purple'],
        'admin_kurikulum'  => ['label' => 'Admin Kurikulum',   'tone' => 'blue'],
        'admin_kesiswaan'  => ['label' => 'Admin Kesiswaan',   'tone' => 'purple'],
        'guru_bk'          => ['label' => 'Guru BK',           'tone' => 'orange'],
        'operator'         => ['label' => 'Operator',          'tone' => 'gray'],
        'siswa'            => ['label' => 'Siswa',             'tone' => 'blue'],
        'orang_tua'        => ['label' => 'Orang Tua',         'tone' => 'orange'],
    ];

    public function index(Request $request)
    {
        $peran  = (string) $request->query('peran', 'semua');
        $status = (string) $request->query('status', 'semua');
        $q      = trim((string) $request->query('q', ''));
        $page   = max(1, (int) $request->query('page', 1));

        // Statistik ringkas
        $stats = [
            'total'              => 0,
            'total_aktif'        => 0,
            'total_nonaktif'     => 0,
            'akun_guru'          => 0,
            'guru_belum_akun'    => 0,
            'akun_ortu'          => 0,
            'ortu_terhubung'     => 0,
        ];

        // Format satu akun:
        // [
        //     'id' => 1, 'nama' => 'Dra. Wahyuni, M.Pd.', 'username' => 'wahyuni',
        //     'peran' => ['admin_kurikulum', 'guru'],   // kode dari const PERAN
        //     'status' => 'aktif',                      // aktif | nonaktif
        //     'terakhir_login' => 'Hari ini, 07.12',    // null jika belum pernah login
        // ]
        $semua = collect();

        $hasil = $semua
            ->when($peran !== 'semua', fn ($c) => $c->filter(
                fn ($a) => in_array($peran, $a['peran'] ?? [], true)
            ))
            ->when($status !== 'semua', fn ($c) => $c->where('status', $status))
            ->when($q !== '', fn ($c) => $c->filter(fn ($a) =>
                str_contains(mb_strtolower($a['nama'] ?? ''), mb_strtolower($q)) ||
                str_contains(mb_strtolower($a['username'] ?? ''), mb_strtolower($q))
            ))
            ->values();

        $totalAkun = $hasil->count();
        $lastPage  = max(1, (int) ceil($totalAkun / self::PER_PAGE));
        $page      = min($page, $lastPage);
        $akun      = $hasil->forPage($page, self::PER_PAGE)->values();

        $peranOpsi = collect(self::PERAN)->map(fn ($p) => $p['label'])->all();

        return view('akun-peran.index', [
            'stats'     => $stats,
            'akun'      => $akun,
            'peranMap'  => self::PERAN,
            'peranOpsi' => $peranOpsi,
            'peran'     => $peran,
            'status'    => $status,
            'q'         => $q,
            'page'      => $page,
            'lastPage'  => $lastPage,
            'totalAkun' => $totalAkun,
        ]);
    }
}