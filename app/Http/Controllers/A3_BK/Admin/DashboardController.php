<?php

namespace App\Http\Controllers\A3_BK\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mapel;
use App\Models\PeriodeTes;
use App\Models\Profesi;
use App\Models\ProgramStudi;
use App\Models\Semester;
use App\Models\SoalRiasec;

/**
 * DashboardController — halaman utama area Administrator.
 */
class DashboardController extends Controller
{
    public function index()
    {
        $semester = Semester::where('aktif', true)->with('tahunAjaran')->first();

        $stats = [
            'total_soal' => SoalRiasec::count(),
            'soal_aktif' => SoalRiasec::where('aktif', true)->count(),
            'total_prodi' => ProgramStudi::count(),
            'total_profesi' => Profesi::count(),
            'total_mapel' => Mapel::whereHas('profilMapel')->count(),
            'periode_aktif' => PeriodeTes::where('status', 'aktif')->count(),
        ];

        $periodeSemesterIni = $semester
            ? PeriodeTes::where('id_semester', $semester->id_semester)->get()
            : collect();

        return view('bk.admin.dashboard', compact('stats', 'periodeSemesterIni', 'semester'));
    }
}
