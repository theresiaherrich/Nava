<?php

namespace App\Http\Controllers\Portal;

use App\Exceptions\AturanBisnisException;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Rapor\RaporController;
use App\Models\PeriodeNilai;
use App\Models\Rapor;
use App\Models\RiwayatKelas;
use App\Models\Rombel;
use App\Models\SiswaRombel;
use App\Models\Tp;
use App\Services\NilaiService;
use App\Services\RaporAccess;
use App\Support\AcademicAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Portal siswa & orang tua. Semua data dibatasi ke siswa itu sendiri / anak yang tertaut.
 * Orang tua dengan > 1 anak mengirim ?id_siswa=.
 */
class PortalController extends Controller
{
    public function __construct(private NilaiService $nilai) {}

    public function anak(): JsonResponse
    {
        if (AcademicAccess::peranIs('SISWA')) {
            return $this->ok([AcademicAccess::siswa()->only(['id_siswa', 'nis', 'nama'])]);
        }

        return $this->ok(AcademicAccess::orangTua()->anak()->get()->map(fn ($s) => $s->only(['id_siswa', 'nis', 'nama']) + ['hubungan' => $s->pivot->hubungan]));
    }

    public function beranda(Request $request): JsonResponse
    {
        $siswa = RaporAccess::siswaPortal($request->integer('id_siswa') ?: null);
        $semester = AcademicAccess::semesterAktif();
        $anggota = RiwayatKelas::with('kelas:id_kelas,nama,tingkat')->where('id_siswa', $siswa->id_siswa)->where('id_semester', $semester->id_semester)->first();
        $mapel = $anggota ? $this->ringkasMapel($anggota) : collect();

        return $this->ok([
            'siswa' => $siswa->only(['id_siswa', 'nis', 'nisn', 'nama']),
            'kelas' => $anggota?->kelas,
            'semester' => $semester->only(['id_semester', 'nama']),
            'rata_rata' => $mapel->whereNotNull('nilai_akhir')->avg('nilai_akhir') !== null ? round($mapel->whereNotNull('nilai_akhir')->avg('nilai_akhir'), 2) : null,
            'jumlah_perlu_penguatan' => $mapel->sum('perlu_penguatan'),
            'rapor_terbit_terbaru' => $this->raporTerbit($siswa->id_siswa)->first(),
        ]);
    }

    /** Nilai per mapel pada semester (default aktif). */
    public function nilai(Request $request): JsonResponse
    {
        $request->validate(['id_semester' => ['nullable', 'integer', 'exists:semester,id_semester']]);
        $siswa = RaporAccess::siswaPortal($request->integer('id_siswa') ?: null);
        $semester = AcademicAccess::semester($request->integer('id_semester') ?: null);
        $anggota = RiwayatKelas::where('id_siswa', $siswa->id_siswa)->where('id_semester', $semester->id_semester)->first();

        return $this->ok($anggota ? $this->ringkasMapel($anggota)->values() : []);
    }

    /** Detail nilai per TP dengan penanda "perlu penguatan" (< KKTP). */
    public function nilaiDetail(Request $request, Rombel $rombel): JsonResponse
    {
        $siswa = RaporAccess::siswaPortal($request->integer('id_siswa') ?: null);
        $peserta = SiswaRombel::where('id_rombel', $rombel->id_rombel)
            ->whereHas('riwayatKelas', fn ($q) => $q->where('id_siswa', $siswa->id_siswa))->firstOrFail();
        $tp = $rombel->tp()->get();
        $n = $this->nilai->nilaiPeserta(collect([$peserta->id_siswa_rombel]))->get($peserta->id_siswa_rombel, collect());
        $sts = PeriodeNilai::where('id_semester', $rombel->id_semester)->where('jenis', 'STS')->value('id_periode');

        return $this->ok([
            'mapel' => $rombel->mapel->only(['id_mapel', 'nama']),
            'ringkasan' => NilaiService::hitung($tp, $n, $sts ? (int) $sts : null),
            'tp' => $tp->map(function (Tp $t) use ($n, $rombel) {
                $v = $n->get($t->id_tp)?->nilai;
                $kktp = $t->kktpEfektif($rombel->mapel);

                return $t->only(['id_tp', 'urutan', 'deskripsi']) + ['kktp' => $kktp, 'nilai' => $v, 'perlu_penguatan' => $v !== null && $v < $kktp];
            })->values(),
        ]);
    }

    public function rapor(Request $request): JsonResponse
    {
        $siswa = RaporAccess::siswaPortal($request->integer('id_siswa') ?: null);

        return $this->ok($this->raporTerbit($siswa->id_siswa)->values());
    }

    /** Hanya rapor milik siswa sendiri/anak dan berstatus terbit. */
    public function unduh(Request $request, Rapor $rapor, RaporController $raporController): Response
    {
        $rapor->load('riwayatKelas.siswa');
        RaporAccess::pastikanBolehLihatSiswa($rapor->riwayatKelas->siswa, $rapor->riwayatKelas->id_semester);
        if ($rapor->status !== 'terbit') {
            throw AturanBisnisException::dilarang('Rapor ini belum diterbitkan.', 'RAPOR_NOT_PUBLISHED');
        }

        return $raporController->pdf($rapor, $request->boolean('pratinjau'));
    }

    private function ringkasMapel(RiwayatKelas $anggota)
    {
        $peserta = SiswaRombel::with(['rombel.mapel', 'rombel.tp'])->where('id_riwayat_kelas', $anggota->id_riwayat_kelas)->get();
        $nilai = $this->nilai->nilaiPeserta($peserta->pluck('id_siswa_rombel'));
        $sts = PeriodeNilai::where('id_semester', $anggota->id_semester)->where('jenis', 'STS')->value('id_periode');

        return $peserta->map(function (SiswaRombel $p) use ($nilai, $sts) {
            $n = $nilai->get($p->id_siswa_rombel, collect());
            $m = $p->rombel->mapel;

            return [
                'id_rombel' => $p->id_rombel,
                'mapel' => $m->only(['id_mapel', 'kode', 'nama']),
                'perlu_penguatan' => $p->rombel->tp->filter(fn (Tp $t) => ($v = $n->get($t->id_tp)?->nilai) !== null && $v < $t->kktpEfektif($m))->count(),
            ] + NilaiService::hitung($p->rombel->tp, $n, $sts ? (int) $sts : null);
        })->sortBy(fn ($x) => $x['mapel']['nama']);
    }

    private function raporTerbit(int $idSiswa)
    {
        return Rapor::with(['periode.semester.tahunAjaran'])->where('status', 'terbit')
            ->whereHas('riwayatKelas', fn ($q) => $q->where('id_siswa', $idSiswa))
            ->orderByDesc('waktu_terbit')->get()
            ->map(fn (Rapor $r) => $r->only(['id_rapor', 'versi', 'waktu_terbit']) + [
                'jenis' => $r->periode->jenis,
                'semester' => $r->periode->semester->nama,
                'tahun_ajaran' => $r->periode->semester->tahunAjaran->nama,
            ]);
    }
}
