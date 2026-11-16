<?php

namespace App\Http\Controllers\Kurikulum;

use App\Http\Controllers\Controller;
use App\Models\Mapel;
use App\Models\PaketNilai;
use App\Models\PeriodeNilai;
use App\Models\Rombel;
use App\Services\PeriodeService;
use App\Support\AcademicAccess;
use Illuminate\Http\JsonResponse;

class DashboardKurikulumController extends Controller
{
    public function __invoke(PeriodeService $periodeService): JsonResponse
    {
        $semester = AcademicAccess::semesterAktif()->load('tahunAjaran');
        $periode = $periodeService->pastikanAda($semester);
        $rombel = Rombel::where('id_semester', $semester->id_semester);

        return $this->ok([
            'semester' => ['id_semester' => $semester->id_semester, 'nama' => $semester->nama, 'tahun_ajaran' => $semester->tahunAjaran->nama],
            'jumlah_mapel' => Mapel::where('aktif', true)->count(),
            'jumlah_rombel' => (clone $rombel)->count(),
            'rombel_tanpa_guru' => (clone $rombel)->doesntHave('guru')->count(),
            'rombel_tanpa_tp' => (clone $rombel)->doesntHave('tp')->count(),
            'periode' => $periode->map(fn (PeriodeNilai $p) => $p->only(['id_periode', 'jenis', 'status', 'status_efektif', 'tgl_buka', 'tgl_tutup', 'tingkat']) + [
                'status_paket' => PaketNilai::where('id_periode', $p->id_periode)->selectRaw('status, count(*) as n')->groupBy('status')->pluck('n', 'status'),
                'kelas_siap_dikunci' => collect($p->status === 'belum_diatur' ? [] : $periodeService->ringkasan($p))->where('status', 'siap_dikunci')->count(),
            ])->values(),
        ]);
    }
}
