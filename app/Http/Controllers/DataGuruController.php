<?php

namespace App\Http\Controllers;

use App\Support\Dummy\Operator;
use App\Support\Dummy\Paginasi;
use Illuminate\Http\Request;

class DataGuruController extends DataSiswaController
{
    /** Data Guru (desain 2.06). Varian: ?modal=impor. */
    public function index(Request $request)
    {
        $semua = Operator::guru();

        $tersaring = Paginasi::saring($semua, [
            $request->query('status') => fn ($r, $v) => $r['status'] === $v,
            $request->query('mapel') => fn ($r, $v) => $r['mapel'] === $v,
        ])->filter(fn ($r) => Paginasi::cocok($request->query('q'), $r['nama'], (string) $r['nip']))->values();

        return view('data-guru.index', [
            'guru' => Paginasi::buat($tersaring, $request),
            'jumlah' => $semua->count(),
            'asn' => $semua->where('status', 'ASN')->count(),
            'gtt' => $semua->where('status', 'GTT')->count(),
            'mapel' => $semua->pluck('mapel')->unique()->sort()->values()->all(),
            'impor' => Operator::hasilImpor('guru'),
        ]);
    }

    /** TODO API: GET /api/master/guru/templat */
    public function format()
    {
        return $this->templat('format_data_guru.csv', ['Nama', 'NIP', 'Status (ASN/GTT)', 'Mapel', 'Email']);
    }
}
