<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RaporSayaController extends Controller
{
    public function index(Request $request)
    {
        $periodeAktif = $request->query('periode') === 'semester' ? 'semester' : 'sts';

        // Data kosong dulu. Nanti diisi dari database.
        // Format: ['url' => 'link PDF', 'tanggal' => '30 Okt', 'versi' => 1, 'nama_file' => 'Rapor_STS.pdf']
        $raporSts      = null;
        $raporSemester = null;

        return view('rapor-saya.index', compact('periodeAktif', 'raporSts', 'raporSemester'));
    }
}