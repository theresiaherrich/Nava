<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\NilaiTp;
use App\Models\PaketNilai;
use App\Models\PeriodeNilai;
use App\Models\Rombel;
use App\Models\SiswaRombel;
use App\Models\VerifikasiNilai;
use App\Models\WaliKelas;
use App\Services\AlokasiTpService;
use App\Services\AuditService;
use App\Services\PenilaianAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Alur: draft → dikirim (guru) → disetujui (wali kelas) → dikunci (kurikulum).
 * Minta revisi: dikirim → perlu_perbaikan. Buka koreksi: dikunci → perlu_perbaikan (wajib alasan).
 * Usulan state machine; belum final sampai disepakati sekolah.
 */
class PaketNilaiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'periode_id' => ['required', 'integer', 'exists:periode_nilai,id'],
            'kelas_id' => ['nullable', 'integer', 'exists:kelas,id'],
        ]);
        $user = $request->user();

        $query = PaketNilai::with(['rombel.mapel', 'kelas'])
            ->where('periode_id', $data['periode_id'])
            ->when($data['kelas_id'] ?? null, fn ($q, $id) => $q->where('kelas_id', $id))
            ->orderBy('id');

        if (! $user->hasAnyRole(['admin', 'kurikulum'])) {
            // Wali kelas hanya melihat paket kelas yang ia wali.
            $kelasWali = WaliKelas::where('guru_id', PenilaianAccess::guruId($user) ?? 0)->pluck('kelas_id');
            $query->whereIn('kelas_id', $kelasWali);
        }

        return response()->json($query->paginate(20));
    }

    /** POST /paket-nilai/kirim  body: rombel_id, periode_id, kelas_id */
    public function kirim(Request $request): JsonResponse
    {
        $data = $request->validate([
            'rombel_id' => ['required', 'integer', 'exists:rombel,id'],
            'periode_id' => ['required', 'integer', 'exists:periode_nilai,id'],
            'kelas_id' => ['required', 'integer', 'exists:kelas,id'],
        ]);
        $user = $request->user();
        $rombel = Rombel::findOrFail($data['rombel_id']);
        $kelas = Kelas::findOrFail($data['kelas_id']);
        abort_unless(PenilaianAccess::bisaInputNilai($user, $rombel), 403, 'Hanya guru pengampu yang dapat mengirim nilai rombel ini.');

        $paket = DB::transaction(function () use ($user, $data, $rombel, $kelas) {
            $rombel = Rombel::whereKey($rombel->id)->lockForUpdate()->firstOrFail();
            $periode = PeriodeNilai::whereKey($data['periode_id'])->lockForUpdate()->firstOrFail();
            abort_unless($periode->terbuka(), 422, 'Periode penilaian tertutup. Nilai tidak dapat dikirim.');
            abort_if((int) $kelas->tingkat !== (int) $periode->tingkat, 422, 'Tingkat kelas tidak sesuai dengan periode.');
            abort_if((int) $kelas->semester_id !== (int) $rombel->semester_id, 422, 'Kelas asal dan rombel berasal dari semester berbeda.');
            abort_if((int) $periode->semester_id !== (int) $rombel->semester_id, 422, 'Semester periode dan rombel berbeda.');

            $paket = PaketNilai::firstOrCreate(
                ['rombel_id' => $rombel->id, 'periode_id' => $periode->id, 'kelas_id' => $kelas->id],
                ['status' => 'draft'],
            );
            $paket = PaketNilai::whereKey($paket->id)->lockForUpdate()->firstOrFail();
            abort_unless(in_array($paket->status, ['draft', 'perlu_perbaikan'], true), 422, "Paket berstatus {$paket->status} dan tidak dapat dikirim.");

            $this->pastikanLengkap($rombel, $periode, $kelas->id);

            $sebelum = $paket->status;
            $paket->update(['status' => 'dikirim', 'dikirim_oleh' => $user->id, 'waktu_kirim' => now()]);
            AuditService::record('kirim_nilai', 'paket_nilai', $paket->id, ['status' => $sebelum], ['status' => 'dikirim'], null, $user->id);

            return $paket;
        });

        return response()->json(['data' => $paket]);
    }

    /** POST /paket-nilai/{record}/setujui  (wali kelas) */
    public function setujui(Request $request, PaketNilai $record): JsonResponse
    {
        abort_unless(PenilaianAccess::bisaMemeriksaKelas($request->user(), $record->kelas_id), 403, 'Anda bukan wali kelas untuk kelas asal paket ini.');
        $data = $request->validate(['catatan' => ['nullable', 'string', 'max:1000']]);

        return response()->json(['data' => $this->ubahStatus($request, $record, 'dikirim', 'disetujui', 'disetujui', 'setujui_nilai', $data['catatan'] ?? null)]);
    }

    /** POST /paket-nilai/{record}/revisi  (wali kelas, catatan wajib) */
    public function revisi(Request $request, PaketNilai $record): JsonResponse
    {
        abort_unless(PenilaianAccess::bisaMemeriksaKelas($request->user(), $record->kelas_id), 403, 'Anda bukan wali kelas untuk kelas asal paket ini.');
        $data = $request->validate(['catatan' => ['required', 'string', 'min:5', 'max:1000']]);

        return response()->json(['data' => $this->ubahStatus($request, $record, 'dikirim', 'perlu_perbaikan', 'perlu_revisi', 'minta_revisi', $data['catatan'])]);
    }

    /** POST /paket-nilai/{record}/kunci  (kurikulum) */
    public function kunci(Request $request, PaketNilai $record): JsonResponse
    {
        return response()->json(['data' => $this->ubahStatus($request, $record, 'disetujui', 'dikunci', 'dikunci', 'kunci_nilai', null)]);
    }

    /** POST /paket-nilai/{record}/buka-koreksi  (kurikulum, alasan wajib) */
    public function bukaKoreksi(Request $request, PaketNilai $record): JsonResponse
    {
        $data = $request->validate(['alasan' => ['required', 'string', 'min:10', 'max:1000']]);

        return response()->json(['data' => $this->ubahStatus($request, $record, 'dikunci', 'perlu_perbaikan', 'buka_koreksi', 'buka_koreksi', $data['alasan'])]);
    }

    private function ubahStatus(Request $request, PaketNilai $record, string $dari, string $ke, string $keputusan, string $aksi, ?string $catatan): PaketNilai
    {
        $user = $request->user();

        return DB::transaction(function () use ($record, $dari, $ke, $keputusan, $aksi, $catatan, $user) {
            $rombel = Rombel::whereKey($record->rombel_id)->lockForUpdate()->firstOrFail();
            $periode = PeriodeNilai::whereKey($record->periode_id)->lockForUpdate()->firstOrFail();
            $paket = PaketNilai::whereKey($record->id)->lockForUpdate()->firstOrFail();
            abort_unless($paket->status === $dari, 422, "Status paket harus '{$dari}', saat ini '{$paket->status}'.");
            if (in_array($ke, ['disetujui', 'dikunci'], true)) {
                $this->pastikanLengkap($rombel, $periode, $paket->kelas_id);
            }

            $sebelum = $paket->status;
            $perubahan = ['status' => $ke];
            if ($ke === 'dikunci') {
                $perubahan += ['dikunci_oleh' => $user->id, 'waktu_kunci' => now()];
            }
            $paket->update($perubahan);

            VerifikasiNilai::create([
                'paket_id' => $paket->id,
                'user_id' => $user->id,
                'keputusan' => $keputusan,
                'catatan' => $catatan,
            ]);
            AuditService::record($aksi, 'paket_nilai', $paket->id, ['status' => $sebelum], ['status' => $ke], $catatan, $user->id);

            return $paket;
        });
    }

    private function pastikanLengkap(Rombel $rombel, PeriodeNilai $periode, int $kelasId): void
    {
        $kelas = Kelas::findOrFail($kelasId);
        abort_unless((int) $periode->semester_id === (int) $rombel->semester_id
            && (int) $kelas->semester_id === (int) $periode->semester_id
            && (int) $kelas->tingkat === (int) $periode->tingkat, 422, 'Konteks akademik paket tidak sesuai.');
        $tpWajib = AlokasiTpService::untukRekap($rombel->tp()->get(), $periode->jenis);
        $peserta = SiswaRombel::where('rombel_id', $rombel->id)
            ->whereHas('siswaKelas', fn ($q) => $q->where('kelas_id', $kelasId)->where('semester_id', $periode->semester_id))->pluck('id');
        $sel = $peserta->count() * $tpWajib->count();
        abort_if($sel === 0, 422, 'Tidak ada TP relevan atau peserta untuk paket periode ini.');
        $terisi = NilaiTp::whereIn('siswa_rombel_id', $peserta)->whereIn('tp_id', $tpWajib->pluck('id'))->whereNotNull('skor')->count();
        abort_if($terisi !== $sel, 422, 'Nilai belum lengkap: seluruh TP yang diperlukan untuk DKN periode ini wajib terisi.');
    }
}
