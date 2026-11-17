<?php

namespace App\Http\Controllers;

use App\Support\Dummy\Operator;
use Illuminate\Http\Request;

class DataKelasController extends Controller
{
    /** Data Kelas & Anggota Kelas (desain 2.07). Varian: ?modal=pindah (2.08). */
    public function index(Request $request)
    {
        $tingkat = in_array($request->query('tingkat'), Operator::TINGKAT, true) ? $request->query('tingkat') : 'X';

        return view('data-kelas.index', [
            'tingkat' => $tingkat,
            'kelas' => Operator::kelas($tingkat),
            // Siswa yang belum punya kelas asal semester ini. TODO API: GET /api/master/siswa?tanpa_kelas=1
            'tanpaKelas' => [
                ['nis' => '2526301', 'nama' => 'Arya Wiratama', 'jenis_kelamin' => 'L', 'agama' => 'Islam'],
                ['nis' => '2526302', 'nama' => 'Bunga Lestari', 'jenis_kelamin' => 'P', 'agama' => 'Kristen'],
                ['nis' => '2526303', 'nama' => 'Cahya Ningrum', 'jenis_kelamin' => 'P', 'agama' => 'Islam'],
                ['nis' => '2526304', 'nama' => 'Damar Pamungkas', 'jenis_kelamin' => 'L', 'agama' => 'Hindu'],
            ],
        ]);
    }
}
