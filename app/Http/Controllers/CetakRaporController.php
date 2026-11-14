<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\PelengkapRapor;
use App\Models\PeriodeNilai;
use App\Models\Rapor;
use App\Models\Siswa;
use App\Models\SiswaKelas;
use App\Services\RaporAccess;
use App\Services\RaporService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/**
 * Bagian 4.8: cetak dan arsip rapor. Wali kelas (kelasnya saja) dan kurikulum/admin.
 * Rapor hanya bisa dicetak bila semua paket nilai kelas pada periode itu berstatus 'dikunci'.
 */
class CetakRaporController extends Controller
{
    /** GET /cetak-rapor?periode_id=&kelas_id=&q= */
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'periode_id' => ['required', 'integer', 'exists:periode_nilai,id'],
            'kelas_id' => ['required', 'integer', 'exists:kelas,id'],
            'q' => ['nullable', 'string', 'max:100'],
        ]);
        [$kelas, $periode] = $this->muat($request, $data);

        $hasil = RaporService::bangun($kelas, $periode);
        $versiTerakhir = Rapor::where('id_periode', $periode->id)->whereIn('id_riwayat_kelas', $hasil['data']->keys())
            ->orderBy('id_rapor')->get()->groupBy('id_riwayat_kelas')->map(fn ($g) => $g->last()->versi);
        $lengkap = PelengkapRapor::where('id_periode', $periode->id)->whereIn('id_riwayat_kelas', $hasil['data']->keys())
            ->get()->keyBy('id_riwayat_kelas');

        $q = mb_strtolower(trim($data['q'] ?? ''));
        $siswa = $hasil['data']->map(fn (array $rapor, int $skId) => [
            'siswa_kelas_id' => $skId,
            'siswa_id' => $rapor['siswa']['id'],
            'nis' => $rapor['siswa']['nis'],
            'nisn' => $rapor['siswa']['nisn'],
            'nama' => $rapor['siswa']['nama'],
            'siap' => $hasil['dikunci'],
            'pelengkap_lengkap' => filled($lengkap->get($skId)?->catatan_wali),
            'versi_terakhir' => $versiTerakhir->get($skId),
            'rapor' => $rapor,
        ])->values()
            ->when($q !== '', fn ($c) => $c->filter(fn ($s) => str_contains(mb_strtolower($s['nama']), $q)
                || str_contains(mb_strtolower((string) $s['nis']), $q)
                || str_contains(mb_strtolower((string) $s['nisn']), $q))->values());

        return response()->json(['data' => [
            'kelas' => ['id' => $kelas->id, 'nama' => $kelas->nama, 'tingkat' => (int) $kelas->tingkat],
            'periode' => ['id' => $periode->id, 'jenis' => $periode->jenis, 'tingkat' => (int) $periode->tingkat],
            'dikunci' => $hasil['dikunci'],
            'jumlah_mapel' => $hasil['jumlah_mapel'],
            'mapel_belum_dikunci' => $hasil['mapel_belum_dikunci'],
            'template_tersedia' => RaporService::cariTemplate($periode) !== null,
            'total_siswa' => $hasil['data']->count(),
            'siap_count' => $hasil['dikunci'] ? $hasil['data']->count() : 0,
            'siswa' => $siswa,
        ]]);
    }

    /** POST /cetak-rapor/siswa/{siswa}/pdf  body: periode_id, kelas_id  → file PDF (application/pdf) */
    public function pdfSiswa(Request $request, Siswa $siswa): Response
    {
        $data = $request->validate([
            'periode_id' => ['required', 'integer', 'exists:periode_nilai,id'],
            'kelas_id' => ['required', 'integer', 'exists:kelas,id'],
        ]);
        [$kelas, $periode] = $this->muat($request, $data);

        $sk = SiswaKelas::where('siswa_id', $siswa->id)->where('kelas_id', $kelas->id)->where('semester_id', $kelas->semester_id)->first();
        if ($sk === null) {
            throw ValidationException::withMessages(['siswa' => 'Siswa bukan peserta kelas ini.']);
        }

        $hasil = RaporService::bangun($kelas, $periode);
        $template = RaporService::pastikanBisaCetak($hasil, $periode);
        [$rapor] = RaporService::arsipkan($sk->id, $periode, $template, $hasil['data'][$sk->id], $request->user());

        return response(Storage::disk('local')->get($rapor->file_pdf), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="rapor_'.$siswa->nis.'_'.$rapor->versi.'.pdf"',
            'X-Rapor-Id' => (string) $rapor->id_rapor,
            'X-Rapor-Versi' => $rapor->versi,
        ]);
    }

    /** POST /cetak-rapor/kelas/pdf  body: periode_id, kelas_id  → satu PDF berisi seluruh siswa kelas */
    public function pdfKelas(Request $request): Response
    {
        $data = $request->validate([
            'periode_id' => ['required', 'integer', 'exists:periode_nilai,id'],
            'kelas_id' => ['required', 'integer', 'exists:kelas,id'],
        ]);
        [$kelas, $periode] = $this->muat($request, $data);

        $hasil = RaporService::bangun($kelas, $periode);
        abort_if($hasil['data']->isEmpty(), 422, 'Kelas ini belum memiliki siswa.');
        $template = RaporService::pastikanBisaCetak($hasil, $periode);

        if (function_exists('set_time_limit')) {
            @set_time_limit(300);
        }
        $items = [];
        foreach ($hasil['data'] as $skId => $rapor) {
            [$arsip] = RaporService::arsipkan($skId, $periode, $template, $rapor, $request->user());
            $items[] = ['data' => $rapor, 'versi' => $arsip->versi];
        }

        return response(RaporService::renderPdf($items, $template), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="rapor_'.preg_replace('/[^A-Za-z0-9_-]+/', '_', $kelas->nama).'_'.$periode->jenis.'.pdf"',
            'X-Rapor-Jumlah' => (string) count($items),
        ]);
    }

    /** GET /cetak-rapor/riwayat?siswa_id=&periode_id=  → arsip rapor (versi) milik satu siswa */
    public function riwayat(Request $request): JsonResponse
    {
        $data = $request->validate([
            'siswa_id' => ['required', 'integer', 'exists:siswa,id'],
            'periode_id' => ['nullable', 'integer', 'exists:periode_nilai,id'],
        ]);
        $user = $request->user();
        $kelasWali = RaporAccess::kelasIdsWali($user);

        if (! RaporAccess::istimewa($user)) {
            $boleh = SiswaKelas::where('siswa_id', $data['siswa_id'])->whereIn('kelas_id', $kelasWali)->exists();
            abort_unless($boleh, 403, 'Siswa ini bukan siswa kelas yang Anda pegang.');
        }

        $rapor = Rapor::with(['siswaKelas.kelas', 'siswaKelas.semester.tahunAjaran', 'periode', 'pencetak'])
            ->whereHas('siswaKelas', fn ($q) => $q->where('siswa_id', $data['siswa_id'])
                ->when(! RaporAccess::istimewa($user), fn ($s) => $s->whereIn('kelas_id', $kelasWali)))
            ->when($data['periode_id'] ?? null, fn ($q, $id) => $q->where('id_periode', $id))
            ->orderByDesc('id_rapor')
            ->get()
            ->map(fn (Rapor $r) => [
                'id' => $r->id_rapor,
                'tanggal' => $r->tanggal_cetak?->toDateString(),
                'jenis' => $r->periode->jenis,
                'versi' => $r->versi,
                'status' => $r->status,
                'kelas' => $r->siswaKelas->kelas->nama,
                'semester' => $r->siswaKelas->semester->nama,
                'tahun_ajaran' => $r->siswaKelas->semester->tahunAjaran?->nama,
                'dicetak_oleh' => $r->pencetak?->name,
                'file_tersedia' => $r->file_pdf !== null && Storage::disk('local')->exists($r->file_pdf),
            ]);

        return response()->json(['data' => $rapor]);
    }

    /** @return array{0: Kelas, 1: PeriodeNilai} */
    private function muat(Request $request, array $data): array
    {
        $kelas = Kelas::findOrFail($data['kelas_id']);
        $periode = PeriodeNilai::findOrFail($data['periode_id']);
        abort_unless(RaporAccess::bisaMelihatKelas($request->user(), $kelas->id), 403, 'Anda tidak berhak mengakses rapor kelas ini.');
        RaporService::pastikanPeriodeCocok($kelas, $periode);

        return [$kelas, $periode];
    }
}
