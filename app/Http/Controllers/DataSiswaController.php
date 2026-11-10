<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DataSiswaController extends Controller
{
    private const PER_PAGE = 10;

    public function index(Request $request)
    {
        $tingkat = (string) $request->query('tingkat', 'semua');
        $kelas   = (string) $request->query('kelas', 'semua');
        $agama   = (string) $request->query('agama', 'semua');
        $q       = trim((string) $request->query('q', ''));
        $page    = max(1, (int) $request->query('page', 1));

        // Format satu siswa:
        // [
        //     'id' => 1, 'nis' => '2526001', 'nisn' => '0081234561', 'nama' => 'Adinda Putri',
        //     'lp' => 'P', 'agama' => 'Islam', 'kelas_asal' => 'X-B',
        //     'orang_tua' => 'terhubung', // terhubung | belum
        // ]
        $semua = collect();

        $hasil = $semua
            ->when($tingkat !== 'semua', fn ($c) => $c->filter(
                fn ($s) => str_starts_with((string) ($s['kelas_asal'] ?? ''), $tingkat . '-')
            ))
            ->when($kelas !== 'semua', fn ($c) => $c->where('kelas_asal', $kelas))
            ->when($agama !== 'semua', fn ($c) => $c->where('agama', $agama))
            ->when($q !== '', fn ($c) => $c->filter(fn ($s) =>
                str_contains(mb_strtolower($s['nama'] ?? ''), mb_strtolower($q)) ||
                str_contains((string) ($s['nis'] ?? ''), $q) ||
                str_contains((string) ($s['nisn'] ?? ''), $q)
            ))
            ->sortBy(fn ($s) => mb_strtolower($s['nama'] ?? ''))
            ->values();

        $totalSiswa = $hasil->count();
        $lastPage   = max(1, (int) ceil($totalSiswa / self::PER_PAGE));
        $page       = min($page, $lastPage);
        $siswa      = $hasil->forPage($page, self::PER_PAGE)->values();

        $daftarKelas = $semua->pluck('kelas_asal')->filter()->unique()->sort()->values();
        $daftarAgama = $semua->pluck('agama')->filter()->unique()->sort()->values();

        return view('data-siswa.index', [
            'siswa'       => $siswa,
            'totalSiswa'  => $totalSiswa,
            'tingkat'     => $tingkat,
            'kelas'       => $kelas,
            'agama'       => $agama,
            'q'           => $q,
            'page'        => $page,
            'lastPage'    => $lastPage,
            'daftarKelas' => $daftarKelas,
            'daftarAgama' => $daftarAgama,
        ]);
    }

    public function format()
    {
        $header = ['NIS', 'NISN', 'Nama', 'L/P', 'Agama', 'Kelas Asal'];

        return response()->streamDownload(function () use ($header) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM supaya Excel membaca UTF-8 dengan benar
            fputcsv($out, $header);
            fclose($out);
        }, 'format_data_siswa.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
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
        $berkas->store('impor/siswa');

        // TODO: baca isi berkas, periksa setiap baris, lalu simpan ke tabel siswa.
        return back()->with('status', 'Berkas ' . $berkas->getClientOriginalName() . ' berhasil diunggah.');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nis'        => ['required', 'string', 'max:20'],
            'nisn'       => ['required', 'string', 'max:20'],
            'nama'       => ['required', 'string', 'max:150'],
            'lp'         => ['required', 'in:L,P'],
            'agama'      => ['required', 'string', 'max:30'],
            'kelas_asal' => ['required', 'string', 'max:10'],
        ]);

        // TODO: simpan $data ke tabel siswa.
        return back()->with('status', 'Data siswa ' . $data['nama'] . ' lolos validasi. Penyimpanan ke database belum dihubungkan.');
    }
}