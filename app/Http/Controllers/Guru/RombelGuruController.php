<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\NilaiTp;
use App\Models\PaketNilai;
use App\Models\PeriodeNilai;
use App\Models\Rombel;
use App\Services\NilaiService;
use App\Services\PenilaianAccess;
use App\Services\PeriodeService;
use App\Support\AcademicAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Dashboard & daftar rombel yang diampu guru login. */
class RombelGuruController extends Controller
{
    public function __construct(private NilaiService $nilai) {}

    public function dashboard(PeriodeService $periodeService): JsonResponse
    {
        $semester = AcademicAccess::semesterAktif();
        $periode = $periodeService->pastikanAda($semester)->first(fn (PeriodeNilai $p) => $p->status_efektif === 'dibuka')
            ?? PeriodeNilai::where('id_semester', $semester->id_semester)->orderBy('jenis', 'desc')->get()->last(fn ($p) => $p->status !== 'belum_diatur');

        $rombel = $this->rombelSaya($semester->id_semester);
        $paket = $periode ? PaketNilai::where('id_periode', $periode->id_periode)->whereIn('id_rombel', $rombel->pluck('id_rombel'))->get()->groupBy('id_rombel') : collect();

        return $this->ok([
            'semester' => $semester->only(['id_semester', 'nama']),
            'periode_aktif' => $periode,
            'jumlah_rombel' => $rombel->count(),
            'jumlah_siswa' => (int) $rombel->sum('peserta_count'),
            'rombel' => $rombel->map(fn (Rombel $r) => [
                'id_rombel' => $r->id_rombel, 'nama' => $r->nama, 'mapel' => $r->mapel->nama,
                'jumlah_siswa' => $r->peserta_count, 'jumlah_tp' => $r->tp_count,
                'progres' => $periode ? $this->progres($r, $periode) : null,
                'status_paket' => $paket->get($r->id_rombel, collect())->pluck('status', 'id_kelas'),
            ])->values(),
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $semester = AcademicAccess::semester($request->integer('id_semester') ?: null);

        return $this->ok($this->rombelSaya($semester->id_semester)->map(fn (Rombel $r) => [
            'id_rombel' => $r->id_rombel, 'nama' => $r->nama, 'mapel' => $r->mapel->only(['id_mapel', 'kode', 'nama']),
            'jumlah_siswa' => $r->peserta_count, 'jumlah_tp' => $r->tp_count,
        ])->values());
    }

    public function show(Rombel $rombel): JsonResponse
    {
        PenilaianAccess::pastikanPengampu($rombel);
        $peserta = $this->nilai->pesertaPeriode($rombel);

        return $this->ok([
            'rombel' => $rombel->only(['id_rombel', 'nama', 'id_semester']),
            'mapel' => $rombel->mapel,
            'kelas_asal' => $peserta->pluck('riwayatKelas.kelas')->unique('id_kelas')->sortBy('nama')->values(),
            'jumlah_siswa' => $peserta->count(),
        ]);
    }

    private function rombelSaya(int $idSemester)
    {
        $guru = AcademicAccess::guru();

        return Rombel::with('mapel:id_mapel,kode,nama')->withCount(['peserta', 'tp'])
            ->where('id_semester', $idSemester)
            ->whereHas('guru', fn ($q) => $q->where('guru.id_guru', $guru->id_guru))
            ->orderBy('nama')->get();
    }

    /** Persentase sel terisi dari TP yang sudah ada (indikator progres, bukan kelengkapan paket). */
    private function progres(Rombel $r, PeriodeNilai $periode): array
    {
        $peserta = $this->nilai->pesertaPeriode($r, $periode);
        $tp = $r->tp()->get()->reject(fn ($t) => $t->hanya_sts && $periode->jenis === 'SAS');
        $total = $peserta->count() * $tp->count();
        $terisi = $total === 0 ? 0 : NilaiTp::whereIn('id_siswa_rombel', $peserta->pluck('id_siswa_rombel'))
            ->whereIn('id_tp', $tp->pluck('id_tp'))->whereNotNull('nilai')->count();

        return ['terisi' => $terisi, 'total' => $total, 'persen' => $total === 0 ? 0 : round($terisi / $total * 100, 1)];
    }
}
