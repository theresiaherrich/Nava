<?php

namespace App\Http\Controllers\Wali;

use App\Http\Controllers\Controller;
use App\Http\Requests\Penilaian\CatatanRequest;
use App\Models\PaketNilai;
use App\Models\PeriodeNilai;
use App\Models\SiswaRombel;
use App\Services\NilaiService;
use App\Services\PaketService;
use App\Services\PenilaianAccess;
use App\Services\PeriodeService;
use App\Support\AcademicAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Verifikasi nilai per mapel oleh wali kelas (hanya kelasnya). */
class VerifikasiController extends Controller
{
    public function __construct(private PaketService $paket, private NilaiService $nilai) {}

    public function index(Request $request, PeriodeService $periodeService): JsonResponse
    {
        $request->validate(['id_periode' => ['required', 'integer', 'exists:periode_nilai,id_periode']]);
        $periode = PeriodeNilai::findOrFail($request->integer('id_periode'));
        $idKelas = AcademicAccess::idKelasWali($periode->id_semester);
        $periodeService->sinkronkanPaket($periode);

        $paket = PaketNilai::with(['rombel.mapel:id_mapel,nama,urutan_rapor', 'rombel.guru:id_guru,nama', 'kelas:id_kelas,nama'])
            ->where('id_periode', $periode->id_periode)->whereIn('id_kelas', $idKelas ?: [0])->get();

        return $this->ok($paket->sortBy(fn ($p) => [$p->kelas->nama, $p->rombel->mapel->urutan_rapor])->values()->map(fn (PaketNilai $p) => [
            'id_paket_nilai' => $p->id_paket_nilai,
            'kelas' => $p->kelas->only(['id_kelas', 'nama']),
            'mapel' => $p->rombel->mapel->nama,
            'rombel' => $p->rombel->nama,
            'guru' => $p->rombel->guru->pluck('nama'),
            'status' => $p->status,
            'lengkap' => $p->lengkap,
            'waktu_kirim' => $p->waktu_kirim?->toIso8601String(),
        ]));
    }

    public function show(PaketNilai $paket): JsonResponse
    {
        $paket->load(['rombel.mapel', 'periode', 'kelas', 'riwayat.user:id_user,nama']);
        PenilaianAccess::pastikanWaliPaket($paket);

        $tp = $paket->rombel->tp()->get();
        $peserta = $this->nilai->pesertaPeriode($paket->rombel, $paket->periode, $paket->id_kelas);
        $nilai = $this->nilai->nilaiPeserta($peserta->pluck('id_siswa_rombel'));
        $sts = $paket->periode->jenis === 'STS' ? $paket->periode->id_periode : PeriodeNilai::where('id_semester', $paket->periode->id_semester)->where('jenis', 'STS')->value('id_periode');

        return $this->ok([
            'paket' => $paket->only(['id_paket_nilai', 'status', 'lengkap', 'koreksi', 'waktu_kirim']),
            'mapel' => $paket->rombel->mapel->nama,
            'kelas' => $paket->kelas->nama,
            'periode' => $paket->periode->only(['id_periode', 'jenis']),
            'tp' => $tp->map(fn ($t) => $t->only(['id_tp', 'urutan', 'deskripsi']) + ['kktp' => $t->kktpEfektif($paket->rombel->mapel)]),
            'kelengkapan' => $this->nilai->kelengkapan($paket->rombel, $paket->periode, $paket->id_kelas),
            'siswa' => $peserta->map(fn (SiswaRombel $p) => [
                'siswa' => $p->riwayatKelas->siswa->only(['id_siswa', 'nis', 'nama']),
                'nilai' => $tp->mapWithKeys(fn ($t) => [$t->id_tp => $nilai->get($p->id_siswa_rombel)?->get($t->id_tp)?->nilai]),
            ] + NilaiService::hitung($tp, $nilai->get($p->id_siswa_rombel, collect()), $sts ? (int) $sts : null)),
            'riwayat' => $paket->riwayat,
        ]);
    }

    public function setujui(CatatanRequest $request, PaketNilai $paket): JsonResponse
    {
        PenilaianAccess::pastikanWaliPaket($paket);

        return $this->ok($this->paket->setujui($paket, $request->input('catatan')), 'Nilai disetujui.');
    }

    public function revisi(CatatanRequest $request, PaketNilai $paket): JsonResponse
    {
        PenilaianAccess::pastikanWaliPaket($paket);

        return $this->ok($this->paket->mintaRevisi($paket, $request->string('catatan')), 'Permintaan revisi dikirim ke guru.');
    }
}
