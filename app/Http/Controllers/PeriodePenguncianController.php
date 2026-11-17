<?php

namespace App\Http\Controllers;

use App\Support\Dummy\Kurikulum;

class PeriodePenguncianController extends Controller
{
    /**
     * Periode & Penguncian (desain 5.01).
     * Varian: ?modal=jadwal (5.02), ?modal=kunci (6.03), ?modal=koreksi (6.04).
     */
    public function index()
    {
        return view('periode-penguncian.index', [
            'periode' => Kurikulum::periode(),
            'kelas' => Kurikulum::statusKelas()->map(fn ($k) => [
                ...collect($k)->only(['kelas', 'tingkat', 'wali', 'disetujui', 'koreksi', 'terkunci_sejak'])->all(),
                'status' => Kurikulum::statusPeriode($k),
            ])->values()->all(),
            'mapelKelas' => Kurikulum::mapelKelas(),
        ]);
    }
}
