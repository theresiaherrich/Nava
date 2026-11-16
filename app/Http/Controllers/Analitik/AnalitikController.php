<?php

namespace App\Http\Controllers\Analitik;

use App\Exceptions\AturanBisnisException;
use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\PeriodeNilai;
use App\Models\Rombel;
use App\Models\Siswa;
use App\Models\SiswaRombel;
use App\Services\AnalitikService;
use App\Services\PenilaianAccess;
use App\Services\RaporAccess;
use App\Support\AcademicAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Analitik dengan cakupan per peran:
 *   KURIKULUM → semua; WALI_KELAS → kelasnya; GURU → rombel yang diampu;
 *   SISWA/ORANG_TUA → tren dirinya/anaknya (lewat /portal/analitik/tren).
 */
class AnalitikController extends Controller
{
    public function __construct(private AnalitikService $analitik) {}

    public function sebaran(Request $request): JsonResponse
    {
        [$peserta, $periode, $label] = $this->cakupan($request);

        return $this->ok(['cakupan' => $label, 'periode' => $periode->only(['id_periode', 'jenis']), 'mapel' => $this->analitik->sebaran($peserta, $periode)]);
    }

    public function ketuntasan(Request $request): JsonResponse
    {
        [$peserta, $periode, $label] = $this->cakupan($request);

        return $this->ok(['cakupan' => $label, 'periode' => $periode->only(['id_periode', 'jenis']), 'mapel' => $this->analitik->ketuntasan($peserta, $periode)]);
    }

    public function earlyWarning(Request $request): JsonResponse
    {
        [$peserta, $periode, $label] = $this->cakupan($request);
        $sts = $periode->jenis === 'STS' ? $periode->id_periode : PeriodeNilai::where('id_semester', $periode->id_semester)->where('jenis', 'STS')->value('id_periode');
        $data = $this->analitik->earlyWarning($peserta, $sts ? (int) $sts : null);

        return $this->ok(['cakupan' => $label, 'jumlah' => count($data), 'siswa' => $data]);
    }

    public function trenSiswa(Siswa $siswa): JsonResponse
    {
        RaporAccess::pastikanBolehLihatSiswa($siswa);
        $hanya = AcademicAccess::peranIs('GURU')
            ? AcademicAccess::guru()->rombel()->pluck('rombel.id_rombel')->map(fn ($id) => (int) $id)->all()
            : null;

        return $this->ok($this->analitik->trenSiswa($siswa, $hanya));
    }

    public function trenPortal(Request $request): JsonResponse
    {
        return $this->ok($this->analitik->trenSiswa(RaporAccess::siswaPortal($request->integer('id_siswa') ?: null)));
    }

    /** @return array{0: Collection<int, SiswaRombel>, 1: PeriodeNilai, 2: array} */
    private function cakupan(Request $request): array
    {
        $request->validate([
            'id_periode' => ['required', 'integer', 'exists:periode_nilai,id_periode'],
            'id_rombel' => ['required_without:id_kelas', 'integer', 'exists:rombel,id_rombel'],
            'id_kelas' => ['required_without:id_rombel', 'integer', 'exists:kelas,id_kelas'],
        ]);
        $periode = PeriodeNilai::findOrFail($request->integer('id_periode'));
        $q = SiswaRombel::query()->whereHas('rombel', fn ($r) => $r->where('id_semester', $periode->id_semester));

        if ($request->filled('id_rombel')) {
            $rombel = Rombel::findOrFail($request->integer('id_rombel'));
            match (AcademicAccess::peranAktif()) {
                'KURIKULUM' => null,
                'GURU' => PenilaianAccess::pastikanPengampu($rombel),
                'WALI_KELAS' => $q->whereHas('riwayatKelas', fn ($w) => $w->whereIn('id_kelas', AcademicAccess::idKelasWali($periode->id_semester) ?: [0])),
                default => throw AturanBisnisException::dilarang('Anda tidak memiliki akses analitik ini.'),
            };
            $q->where('id_rombel', $rombel->id_rombel);
            $label = ['jenis' => 'rombel', 'id' => $rombel->id_rombel, 'nama' => $rombel->nama];
        } else {
            $kelas = Kelas::findOrFail($request->integer('id_kelas'));
            match (AcademicAccess::peranAktif()) {
                'KURIKULUM' => null,
                'WALI_KELAS' => PenilaianAccess::pastikanWaliKelas($kelas->id_kelas, $periode->id_semester),
                'GURU' => $q->whereHas('rombel.guru', fn ($g) => $g->where('guru.id_guru', AcademicAccess::guru()->id_guru)),
                default => throw AturanBisnisException::dilarang('Anda tidak memiliki akses analitik ini.'),
            };
            $q->whereHas('riwayatKelas', fn ($w) => $w->where('id_kelas', $kelas->id_kelas)->where('id_semester', $periode->id_semester));
            $label = ['jenis' => 'kelas', 'id' => $kelas->id_kelas, 'nama' => $kelas->nama];
        }

        // Hanya tingkat yang ikut periode.
        $q->whereHas('riwayatKelas.kelas', fn ($k) => $k->whereIn('tingkat', $periode->tingkat ?: ['-']));

        return [$q->get(), $periode, $label];
    }
}
