<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DataGuruController extends Controller
{
    private const PER_PAGE = 10;

    // Peran yang tersedia beserta warna label
    public const PERAN = [
        'guru'            => ['label' => 'Guru',            'tone' => 'gray'],
        'wali_kelas'      => ['label' => 'Wali Kelas',      'tone' => 'purple'],
        'admin_kurikulum' => ['label' => 'Admin Kurikulum', 'tone' => 'green'],
        'admin_kesiswaan' => ['label' => 'Admin Kesiswaan', 'tone' => 'blue'],
        'guru_bk'         => ['label' => 'Guru BK',         'tone' => 'orange'],
    ];

    public function index(Request $request)
    {
        $status = (string) $request->query('status', 'semua');
        $mapel  = (string) $request->query('mapel', 'semua');
        $q      = trim((string) $request->query('q', ''));
        $page   = max(1, (int) $request->query('page', 1));

        // Format satu guru:
        // [
        //     'id' => 1, 'nama' => 'Agus Prasetyo, S.Pd.', 'nip' => '198503122010011004',
        //     'status' => 'asn',                  // asn | gtt
        //     'peran' => ['guru', 'wali_kelas'],  // kode dari const PERAN
        //     'mapel' => 'B. Inggris',
        //     'akun' => 'aktif',                  // aktif | nonaktif | belum
        // ]
        $semua = collect();

        $hasil = $semua
            ->when($status !== 'semua', fn ($c) => $c->where('status', $status))
            ->when($mapel !== 'semua', fn ($c) => $c->where('mapel', $mapel))
            ->when($q !== '', fn ($c) => $c->filter(fn ($g) =>
                str_contains(mb_strtolower($g['nama'] ?? ''), mb_strtolower($q)) ||
                str_contains((string) ($g['nip'] ?? ''), $q)
            ))
            ->sortBy(fn ($g) => mb_strtolower($g['nama'] ?? ''))
            ->values();

        $totalGuru = $hasil->count();
        $lastPage  = max(1, (int) ceil($totalGuru / self::PER_PAGE));
        $page      = min($page, $lastPage);
        $guru      = $hasil->forPage($page, self::PER_PAGE)->values();

        $statistik = [
            'asn' => $semua->where('status', 'asn')->count(),
            'gtt' => $semua->where('status', 'gtt')->count(),
        ];

        $daftarMapel = $semua->pluck('mapel')->filter()->unique()->sort()->values();

        return view('data-guru.index', [
            'guru'        => $guru,
            'totalGuru'   => $totalGuru,
            'statistik'   => $statistik,
            'daftarMapel' => $daftarMapel,
            'peranMap'    => self::PERAN,
            'status'      => $status,
            'mapel'       => $mapel,
            'q'           => $q,
            'page'        => $page,
            'lastPage'    => $lastPage,
        ]);
    }

    public function format()
    {
        $header = ['NIP', 'Nama Lengkap', 'Status', 'Mapel Diampu'];

        return response()->streamDownload(function () use ($header) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, $header);
            fclose($out);
        }, 'format_data_guru.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function impor(Request $request)
    {
        $request->validate([
            'berkas' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
        ], [
            'berkas.required' => 'Pilih berkas Excel terlebih dahulu.',
            'berkas.mimes'    => 'Format berkas harus .xlsx, .xls, atau .csv.',
            'berkas.max'      => 'Ukuran berkas maksimal 5 MB.',
        ]);

        $berkas = $request->file('berkas');
        $berkas->store('impor/guru');

        // TODO: baca isi berkas, periksa setiap baris, lalu simpan ke tabel guru.
        return back()->with('status', 'Berkas ' . $berkas->getClientOriginalName() . ' berhasil diunggah.');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama'   => ['required', 'string', 'max:150'],
            'nip'    => ['nullable', 'digits:18'],
            'status' => ['required', 'in:asn,gtt'],
            'mapel'  => ['nullable', 'string', 'max:60'],
        ]);

        // TODO: simpan $data ke tabel guru.
        return back()->with('status', 'Data guru ' . $data['nama'] . ' lolos validasi. Penyimpanan ke database belum dihubungkan.');
    }
}