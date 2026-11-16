<?php

namespace App\Http\Controllers\Kurikulum;

use App\Http\Controllers\Controller;
use App\Http\Requests\Penilaian\AturPeriodeRequest;
use App\Http\Requests\Penilaian\CatatanRequest;
use App\Http\Requests\Penilaian\KktpRequest;
use App\Models\Kelas;
use App\Models\PaketNilai;
use App\Models\PeriodeNilai;
use App\Models\Tp;
use App\Services\PeriodeService;
use App\Services\TpService;
use App\Support\AcademicAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Periode & penguncian, KKTP (Kurikulum). */
class PeriodeController extends Controller
{
    public function __construct(private PeriodeService $periode) {}

    public function index(Request $request): JsonResponse
    {
        $semester = AcademicAccess::semester($request->integer('id_semester') ?: null);

        return $this->ok($this->periode->pastikanAda($semester));
    }

    public function atur(AturPeriodeRequest $request, PeriodeNilai $periode): JsonResponse
    {
        return $this->ok($this->periode->atur($periode, $request->input('tgl_buka'), $request->input('tgl_tutup'), $request->input('tingkat')), 'Jadwal periode disimpan.');
    }

    public function tutup(PeriodeNilai $periode): JsonResponse
    {
        return $this->ok($this->periode->tutup($periode), 'Periode ditutup.');
    }

    public function ringkasan(PeriodeNilai $periode): JsonResponse
    {
        return $this->ok(['periode' => $periode, 'kelas' => $this->periode->ringkasan($periode)]);
    }

    public function kunciKelas(PeriodeNilai $periode, Kelas $kelas): JsonResponse
    {
        $n = $this->periode->kunciKelas($periode, $kelas);

        return $this->ok(['jumlah_mapel' => $n, 'periode' => $periode->fresh()], "Nilai kelas {$kelas->nama} dikunci ({$n} mapel).");
    }

    public function kunciSemuaSiap(PeriodeNilai $periode): JsonResponse
    {
        $kelas = $this->periode->kunciSemuaSiap($periode);

        return $this->ok(['kelas_dikunci' => $kelas, 'periode' => $periode->fresh()], count($kelas).' kelas dikunci.');
    }

    public function bukaKoreksi(CatatanRequest $request, PaketNilai $paket): JsonResponse
    {
        return $this->ok($this->periode->bukaKoreksi($paket, $request->string('catatan')), 'Koreksi dibuka. Guru dapat memperbaiki nilai lalu mengirim ulang.');
    }

    public function aturKktp(KktpRequest $request, Tp $tp, TpService $tpService): JsonResponse
    {
        $kktp = $request->input('kktp');

        return $this->ok($tpService->aturKktp($tp, $kktp === null ? null : (float) $kktp), 'KKTP disimpan.');
    }
}
