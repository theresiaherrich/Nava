<?php

namespace App\Http\Controllers\Wali;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\PaketNilai;
use App\Models\PeriodeNilai;
use App\Models\RiwayatKelas;
use App\Services\PeriodeService;
use App\Support\AcademicAccess;
use Illuminate\Http\JsonResponse;

/** Dashboard wali kelas: kelas yang diwalikan dan status verifikasi per periode. */
class KelasWaliController extends Controller
{
    public function dashboard(PeriodeService $periodeService): JsonResponse
    {
        $semester = AcademicAccess::semesterAktif();
        $periode = $periodeService->pastikanAda($semester);
        $kelas = Kelas::whereKey(AcademicAccess::idKelasWali($semester->id_semester))->orderBy('nama')->get();

        return $this->ok([
            'semester' => $semester->only(['id_semester', 'nama']),
            'periode' => $periode,
            'kelas' => $kelas->map(fn (Kelas $k) => $k->only(['id_kelas', 'nama', 'tingkat']) + [
                'jumlah_siswa' => RiwayatKelas::where('id_kelas', $k->id_kelas)->where('id_semester', $semester->id_semester)->count(),
                'verifikasi' => $periode->filter(fn (PeriodeNilai $p) => $p->ikutTingkat($k->tingkat))->mapWithKeys(fn (PeriodeNilai $p) => [
                    $p->jenis => PaketNilai::where('id_periode', $p->id_periode)->where('id_kelas', $k->id_kelas)
                        ->selectRaw('status, count(*) as n')->groupBy('status')->pluck('n', 'status'),
                ]),
            ])->values(),
        ]);
    }
}
