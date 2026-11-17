<?php

namespace App\Http\Controllers;

use App\Support\Dummy\Operator;
use App\Support\Dummy\Paginasi;
use Illuminate\Http\Request;

class DataSiswaController extends Controller
{
    /** Data Siswa (desain 2.04). Varian: ?modal=impor (2.05). */
    public function index(Request $request)
    {
        $semua = Operator::siswa();

        $tersaring = Paginasi::saring($semua, [
            $request->query('tingkat') => fn ($r, $v) => $r['tingkat'] === $v,
            $request->query('kelas') => fn ($r, $v) => $r['kelas'] === $v,
            $request->query('agama') => fn ($r, $v) => $r['agama'] === $v,
            $request->query('ortu') => fn ($r, $v) => $r['orang_tua'] === $v,
        ])->filter(fn ($r) => Paginasi::cocok($request->query('q'), $r['nama'], $r['nis'], $r['nisn']))->values();

        return view('data-siswa.index', [
            'siswa' => Paginasi::buat($tersaring, $request),
            'jumlahAktif' => $semua->count(),
            'kelas' => collect(Operator::TINGKAT)->flatMap(fn ($t) => array_map(fn ($k) => "$t-$k", Operator::KELAS))->all(),
            'agama' => Operator::AGAMA,
            'impor' => Operator::hasilImpor('siswa'),
        ]);
    }

    /** Templat impor. TODO API: GET /api/master/siswa/templat (xlsx dari backend). */
    public function format()
    {
        return $this->templat('format_data_siswa.csv', ['NIS', 'NISN', 'Nama', 'L/P', 'Agama', 'Kelas Asal']);
    }

    protected function templat(string $nama, array $header)
    {
        return response()->streamDownload(function () use ($header) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM supaya Excel membaca UTF-8 dengan benar
            fputcsv($out, $header);
            fclose($out);
        }, $nama, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
