<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class RekapNilaiController extends Controller
{
    public function index(Request $request)
    {
        // TODO: ganti dengan query database
        return view('rekap-nilai.index', [
            'rombels'       => collect(),   // rombel yang diampu: id, nama, jumlah_siswa, jumlah_tp, kelengkapan, rata_rata, status
            'statistik'     => (object) [
                'rombel_id'         => null,
                'rombel_aktif'      => null,
                'jumlah_siswa'      => 0,
                'rata_rata'         => null,
                'rata_rata_selisih' => null,
                'kelengkapan'       => 0,
                'di_bawah_kktp'     => null,
            ],
            'daftarRombel'  => collect(),   // untuk dropdown: id, label
            'siswaList'     => new LengthAwarePaginator(
                collect(),
                0,
                10,
                $request->integer('page', 1),
                ['path' => $request->url(), 'query' => $request->query()]
            ),
            'semesterLabel' => null,        // mis. 'Semester Ganjil 2026/2027'
            'kktp'          => 75,
        ]);
    }
}