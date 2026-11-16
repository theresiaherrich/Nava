<?php

namespace App\Http\Controllers\Wali;

use App\Http\Controllers\Controller;
use App\Http\Requests\Rapor\PelengkapRequest;
use App\Http\Requests\Rapor\TarikPresensiRequest;
use App\Models\Kelas;
use App\Models\PeriodeNilai;
use App\Services\PelengkapService;
use App\Services\PenilaianAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Pelengkap rapor: presensi ringkas, catatan wali, kokurikuler (hanya kelas yang diwalikan). */
class PelengkapController extends Controller
{
    public function __construct(private PelengkapService $pelengkap) {}

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'id_kelas' => ['required', 'integer', 'exists:kelas,id_kelas'],
            'id_periode' => ['required', 'integer', 'exists:periode_nilai,id_periode'],
        ]);
        [$kelas, $periode] = $this->konteks($request->integer('id_kelas'), $request->integer('id_periode'));

        return $this->ok($this->pelengkap->daftar($kelas, $periode));
    }

    public function simpan(PelengkapRequest $request): JsonResponse
    {
        [$kelas, $periode] = $this->konteks($request->integer('id_kelas'), $request->integer('id_periode'));
        $n = $this->pelengkap->simpan($kelas, $periode, $request->validated('items'));

        return $this->ok(['tersimpan' => $n, 'waktu' => now()->toIso8601String()], 'Pelengkap rapor tersimpan.');
    }

    public function tarikPresensi(TarikPresensiRequest $request): JsonResponse
    {
        [$kelas, $periode] = $this->konteks($request->integer('id_kelas'), $request->integer('id_periode'));
        $n = $this->pelengkap->tarikPresensi($kelas, $periode, $request->input('dari'), $request->input('sampai'));

        return $this->ok(['jumlah_siswa' => $n], 'Ringkasan presensi diambil dari modul A1.');
    }

    /** @return array{0: Kelas, 1: PeriodeNilai} */
    private function konteks(int $idKelas, int $idPeriode): array
    {
        $kelas = Kelas::findOrFail($idKelas);
        $periode = PeriodeNilai::findOrFail($idPeriode);
        PenilaianAccess::pastikanWaliKelas($kelas->id_kelas, $periode->id_semester);

        return [$kelas, $periode];
    }
}
