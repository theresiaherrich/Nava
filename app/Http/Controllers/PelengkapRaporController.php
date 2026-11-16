<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\PelengkapRapor;
use App\Models\PeriodeNilai;
use App\Models\PesertaEkskul;
use App\Models\Rapor;
use App\Models\Siswa;
use App\Models\SiswaKelas;
use App\Services\AuditService;
use App\Services\RaporAccess;
use App\Services\RaporService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Bagian 4.7: pelengkap rapor. Wali kelas menulis catatan untuk kelasnya; kurikulum hanya membaca.
 * Kehadiran (sakit/izin/alpha) milik A1: hanya dibaca, tidak pernah ditulis atau diisi nol di sini.
 */
class PelengkapRaporController extends Controller
{
    /** GET /pelengkap-rapor?periode_id=&kelas_id=&q=&status=semua|belum|lengkap */
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'periode_id' => ['required', 'integer', 'exists:periode_nilai,id'],
            'kelas_id' => ['required', 'integer', 'exists:kelas,id'],
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:semua,belum,lengkap'],
        ]);
        [$kelas, $periode] = $this->muat($data);
        abort_unless(RaporAccess::bisaMelihatKelas($request->user(), $kelas->id), 403, 'Anda tidak berhak melihat pelengkap rapor kelas ini.');

        $semua = RaporService::siswaKelas($kelas);
        $pelengkap = PelengkapRapor::where('id_periode', $periode->id)->whereIn('id_riwayat_kelas', $semua->pluck('id'))->get()->keyBy('id_riwayat_kelas');
        $ekskul = PesertaEkskul::with(['ekskul', 'predikat'])
            ->where('id_semester', $kelas->semester_id)
            ->whereIn('id_siswa', $semua->pluck('siswa_id'))
            ->get()->groupBy('id_siswa');

        $baris = $semua->map(function (SiswaKelas $sk) use ($pelengkap, $ekskul) {
            $p = $pelengkap->get($sk->id);
            $daftarEkskul = ($ekskul->get($sk->siswa_id) ?? collect())
                ->map(fn (PesertaEkskul $e) => ['nama' => $e->ekskul->nama, 'nilai' => $e->predikat->predikat])
                ->values()->all();

            return [
                'id' => $sk->siswa_id,
                'siswa_kelas_id' => $sk->id,
                'nis' => $sk->siswa->nis,
                'nisn' => $sk->siswa->nisn,
                'nama' => $sk->siswa->nama,
                'catatan_wali' => $p?->catatan_wali,
                'kokurikuler' => $p?->kokurikuler,
                // null = data A1 belum ada (bukan 0).
                'sakit' => $p?->sakit,
                'izin' => $p?->izin,
                'alpha' => $p?->alpha,
                'ekskul' => $daftarEkskul,
                'lengkap' => filled($p?->catatan_wali),
                'diperbarui' => $p?->updated_at?->toIso8601String(),
            ];
        });

        $total = $baris->count();
        $kehadiranTerisi = $total > 0 && $baris->every(fn ($b) => $b['sakit'] !== null && $b['izin'] !== null && $b['alpha'] !== null);
        $ringkasan = [
            'total_siswa' => $total,
            'catatan_terisi' => $baris->where('lengkap', true)->count(),
            'ekskul_terisi' => $baris->filter(fn ($b) => $b['ekskul'] !== [])->count(),
            'prestasi_belum_dinilai' => $baris->filter(fn ($b) => $b['ekskul'] === [])->count(),
            'lengkap' => $baris->where('lengkap', true)->count(),
            'kehadiran_terisi' => $kehadiranTerisi,
            'kehadiran_sumber' => $kehadiranTerisi ? 'pelengkap_rapor' : null,
            'terakhir_tersimpan' => $pelengkap->sortByDesc('updated_at')->first()?->updated_at?->toIso8601String(),
        ];

        $q = mb_strtolower(trim($data['q'] ?? ''));
        $status = $data['status'] ?? 'semua';
        $tampil = $baris
            ->when($q !== '', fn ($c) => $c->filter(fn ($b) => str_contains(mb_strtolower($b['nama']), $q)
                || str_contains(mb_strtolower((string) $b['nis']), $q)
                || str_contains(mb_strtolower((string) $b['nisn']), $q)))
            ->when($status === 'belum', fn ($c) => $c->where('lengkap', false))
            ->when($status === 'lengkap', fn ($c) => $c->where('lengkap', true))
            ->values();

        return response()->json(['data' => [
            'kelas' => ['id' => $kelas->id, 'nama' => $kelas->nama, 'tingkat' => (int) $kelas->tingkat],
            'periode' => ['id' => $periode->id, 'jenis' => $periode->jenis, 'tingkat' => (int) $periode->tingkat, 'dikunci' => (bool) $periode->dikunci],
            'ringkasan' => $ringkasan,
            'siswa' => $tampil,
        ]]);
    }

    /** POST /pelengkap-rapor/siswa/{siswa}/catatan  body: periode_id, kelas_id, catatan (autosave satu siswa) */
    public function catatan(Request $request, Siswa $siswa): JsonResponse
    {
        $data = $request->validate([
            'periode_id' => ['required', 'integer', 'exists:periode_nilai,id'],
            'kelas_id' => ['required', 'integer', 'exists:kelas,id'],
            'catatan' => ['present', 'nullable', 'string', 'max:300'],
        ]);
        [$kelas, $periode] = $this->muat($data);
        abort_unless(RaporAccess::bisaMengisiKelas($request->user(), $kelas->id), 403, 'Hanya wali kelas dari kelas ini yang dapat mengisi catatan.');

        $sk = SiswaKelas::where('siswa_id', $siswa->id)->where('kelas_id', $kelas->id)->where('semester_id', $kelas->semester_id)->first();
        if ($sk === null) {
            throw ValidationException::withMessages(['siswa' => 'Siswa bukan peserta kelas ini.']);
        }
        $this->pastikanBelumTerbit([$sk->id], $periode);

        $catatan = isset($data['catatan']) ? trim($data['catatan']) : null;
        $p = PelengkapRapor::updateOrCreate(
            ['id_riwayat_kelas' => $sk->id, 'id_periode' => $periode->id],
            ['catatan_wali' => $catatan === '' ? null : $catatan],
        );

        return response()->json(['data' => [
            'siswa_id' => $siswa->id,
            'catatan_wali' => $p->catatan_wali,
            'lengkap' => filled($p->catatan_wali),
            'tersimpan_pada' => $p->updated_at->toIso8601String(),
        ]]);
    }

    /** POST /pelengkap-rapor/simpan  body: periode_id, kelas_id, items[{siswa_id, catatan, kokurikuler?}] */
    public function simpan(Request $request): JsonResponse
    {
        $data = $request->validate([
            'periode_id' => ['required', 'integer', 'exists:periode_nilai,id'],
            'kelas_id' => ['required', 'integer', 'exists:kelas,id'],
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.siswa_id' => ['required', 'integer', 'distinct', 'exists:siswa,id'],
            'items.*.catatan' => ['present', 'nullable', 'string', 'max:300'],
            'items.*.kokurikuler' => ['nullable', 'string', 'max:1000'],
        ]);
        [$kelas, $periode] = $this->muat($data);
        $user = $request->user();
        abort_unless(RaporAccess::bisaMengisiKelas($user, $kelas->id), 403, 'Hanya wali kelas dari kelas ini yang dapat menyimpan pelengkap rapor.');

        $jumlah = DB::transaction(function () use ($data, $kelas, $periode, $user) {
            $skPerSiswa = SiswaKelas::where('kelas_id', $kelas->id)->where('semester_id', $kelas->semester_id)->pluck('id', 'siswa_id');
            $asing = collect($data['items'])->pluck('siswa_id')->reject(fn ($id) => $skPerSiswa->has($id));
            if ($asing->isNotEmpty()) {
                throw ValidationException::withMessages(['items' => 'Sebagian siswa bukan peserta kelas ini.']);
            }
            $this->pastikanBelumTerbit(collect($data['items'])->map(fn ($i) => $skPerSiswa[$i['siswa_id']])->all(), $periode);

            foreach ($data['items'] as $item) {
                $nilai = ['catatan_wali' => ($item['catatan'] ?? null) === null ? null : trim($item['catatan'])];
                if ($nilai['catatan_wali'] === '') {
                    $nilai['catatan_wali'] = null;
                }
                // Kokurikuler hanya diubah bila dikirim.
                if (array_key_exists('kokurikuler', $item)) {
                    $nilai['kokurikuler'] = $item['kokurikuler'] ?? null;
                }
                PelengkapRapor::updateOrCreate(
                    ['id_riwayat_kelas' => $skPerSiswa[$item['siswa_id']], 'id_periode' => $periode->id],
                    $nilai,
                );
            }
            AuditService::record('simpan_pelengkap_rapor', 'pelengkap_rapor', $periode->id, null, ['kelas_id' => $kelas->id, 'jumlah_siswa' => count($data['items'])], null, $user->id);

            return count($data['items']);
        });

        return response()->json(['data' => ['tersimpan' => $jumlah, 'waktu' => now()->toIso8601String()]]);
    }

    /** @return array{0: Kelas, 1: PeriodeNilai} */
    private function muat(array $data): array
    {
        $kelas = Kelas::findOrFail($data['kelas_id']);
        $periode = PeriodeNilai::findOrFail($data['periode_id']);
        RaporService::pastikanPeriodeCocok($kelas, $periode);

        return [$kelas, $periode];
    }

    /** Catatan tidak boleh berubah setelah rapor diterbitkan (status 'terbit'). */
    private function pastikanBelumTerbit(array $siswaKelasIds, PeriodeNilai $periode): void
    {
        $terbit = Rapor::whereIn('id_riwayat_kelas', $siswaKelasIds)->where('id_periode', $periode->id)->where('status', 'terbit')->exists();
        abort_if($terbit, 422, 'Rapor sudah diterbitkan. Pelengkap rapor tidak dapat diubah.');
    }
}
