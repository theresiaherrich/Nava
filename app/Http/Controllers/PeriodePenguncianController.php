<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PeriodePenguncianController extends Controller
{
    public function index(Request $request)
    {
        // TODO: ganti dengan query database
        return view('periode-penguncian.index', [
            'semesterTabs'  => [],   // mis. ['Semester Ganjil 2026/2027', 'Semester Genap 2026/2027']
            'semesterAktif' => 0,    // index semester yang aktif
            'sts'           => null, // ['judul', 'tanggal', 'statusLabel']
            'tingkatAktif'  => null, // mis. 'X'
            'tingkatData'   => [],   // ['X' => ['keseluruhan' => 87, 'kelas' => [['kode' => 'X-A', 'persen' => 100]]]]
            'sas'           => null, // ['judul', 'tanggal', 'statusLabel', 'tanggalBuka', 'bulanBuka', 'hariSampaiDibuka']
        ]);
    }
}