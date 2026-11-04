<?php

namespace App\Http\Controllers;

use App\Models\NilaiTp;
use App\Models\PaketNilai;
use App\Models\PeriodeNilai;
use App\Models\Rombel;
use App\Models\SiswaRombel;
use App\Services\AuditService;
use App\Services\PenilaianAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NilaiController extends Controller
{
    /** GET /rombel/{record}/nilai?periode_id=&kelas_id= */
    public function index(Request $request, Rombel $record): JsonResponse
    {
        abort_unless(PenilaianAccess::bisaKelolaRombel($request->user(), $record), 403, 'Rombel ini bukan rombel yang Anda ampu.');
        $data = $request->validate([
            'periode_id' => ['required', 'integer', 'exists:periode_nilai,id'],
            'kelas_id' => ['nullable', 'integer', 'exists:kelas,id'],
        ]);

        $periode = PeriodeNilai::findOrFail($data['periode_id']);
        $tp = $record->tp()->get();
        $peserta = SiswaRombel::with(['siswaKelas.siswa', 'siswaKelas.kelas'])
            ->where('rombel_id', $record->id)
            ->when($data['kelas_id'] ?? null, fn ($q, $kelasId) => $q->whereHas('siswaKelas', fn ($s) => $s->where('kelas_id', $kelasId)))
            ->get()
            ->sortBy(fn ($p) => $p->siswaKelas?->siswa?->nama)
            ->values();

        $nilai = NilaiTp::whereIn('siswa_rombel_id', $peserta->pluck('id'))->get()
            ->groupBy('siswa_rombel_id')
            ->map(fn ($grup) => $grup->mapWithKeys(fn ($n) => [$n->tp_id => $n->skor]));

        return response()->json(['data' => [
            'periode' => $periode,
            'tp' => $tp,
            'tp_dapat_diisi' => $tp->filter(fn ($t) => PenilaianAccess::tpDapatDiisi($periode, $t))->pluck('id')->values(),
            'peserta' => $peserta,
            'nilai' => $nilai,   // null = belum diisi (BUKAN 0)
            'status_paket' => PaketNilai::where('rombel_id', $record->id)->where('periode_id', $periode->id)->get(['kelas_id', 'status']),
        ]]);
    }

    /**
     * PUT /rombel/{record}/nilai  — dipakai untuk autosave satu sel maupun tempel dari Excel.
     * body: periode_id, items[{siswa_rombel_id, tp_id, skor|null}]
     */
    public function simpan(Request $request, Rombel $record): JsonResponse
    {
        $user = $request->user();
        abort_unless(PenilaianAccess::bisaInputNilai($user, $record), 403, 'Hanya guru pengampu rombel ini yang dapat mengisi nilai.');

        $data = $request->validate([
            'periode_id' => ['required', 'integer', 'exists:periode_nilai,id'],
            'items' => ['required', 'array', 'min:1', 'max:1000'],
            'items.*.siswa_rombel_id' => ['required', 'integer', 'exists:siswa_rombel,id'],
            'items.*.tp_id' => ['required', 'integer', 'exists:tp,id'],
            'items.*.skor' => ['nullable', 'numeric', 'between:0,100'],
        ]);

        $jumlah = DB::transaction(function () use ($record, $data, $user) {
            $periode = PeriodeNilai::whereKey($data['periode_id'])->lockForUpdate()->firstOrFail();
            abort_unless($periode->terbuka(), 422, 'Periode penilaian sedang tertutup atau sudah dikunci.');

            $tpMap = $record->tp()->get()->keyBy('id');
            $pesertaMap = SiswaRombel::with('siswaKelas')->where('rombel_id', $record->id)->get()->keyBy('id');

            $kelasIds = [];
            foreach ($data['items'] as $item) {
                $tp = $tpMap->get($item['tp_id']);
                $peserta = $pesertaMap->get($item['siswa_rombel_id']);
                abort_if($tp === null || $peserta === null, 422, 'TP atau peserta tidak berada dalam rombel ini.');
                abort_unless(PenilaianAccess::tpDapatDiisi($periode, $tp), 422, "TP ke-{$tp->nomor_urut} tidak dapat diisi pada periode {$periode->jenis}.");
                $kelasIds[(int) $peserta->siswaKelas->kelas_id] = true;
            }

            // Nilai pada paket yang sudah dikirim/disetujui/dikunci tidak boleh berubah.
            $terkunci = PaketNilai::where('rombel_id', $record->id)
                ->where('periode_id', $periode->id)
                ->whereIn('kelas_id', array_keys($kelasIds))
                ->whereNotIn('status', ['draft', 'perlu_perbaikan'])
                ->exists();
            abort_if($terkunci, 422, 'Nilai pada paket kelas ini sudah dikirim atau dikunci.');

            foreach ($data['items'] as $item) {
                NilaiTp::updateOrCreate(
                    ['tp_id' => $item['tp_id'], 'siswa_rombel_id' => $item['siswa_rombel_id']],
                    ['skor' => $item['skor'] ?? null, 'diubah_oleh' => $user->id],
                );
            }

            AuditService::record('simpan_nilai', 'nilai_tp', $periode->id, null, ['rombel_id' => $record->id, 'jumlah_sel' => count($data['items'])], null, $user->id);

            return count($data['items']);
        });

        return response()->json(['data' => ['tersimpan' => $jumlah, 'waktu' => now()->toIso8601String()]]);
    }
}
