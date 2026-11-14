<?php

namespace App\Http\Controllers;

use App\Http\Requests\TpRequest;
use App\Models\Rombel;
use App\Models\Tp;
use App\Services\AuditService;
use App\Services\PenilaianAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TpController extends Controller
{
    public function index(Request $request, Rombel $record): JsonResponse
    {
        abort_unless(PenilaianAccess::bisaKelolaRombel($request->user(), $record), 403, 'Rombel ini bukan rombel yang Anda ampu.');

        return response()->json(['data' => $record->tp()->get()]);
    }

    public function store(TpRequest $request, Rombel $record): JsonResponse
    {
        $data = $request->validated();

        $tp = DB::transaction(function () use ($record, $data) {
            $rombel = Rombel::whereKey($record->id)->lockForUpdate()->firstOrFail();
            PenilaianAccess::pastikanRombelBisaDiubah($rombel);
            abort_if(Tp::where('rombel_id', $rombel->id)->count() >= PenilaianAccess::MAKS_TP, 422, 'TP sudah 10 (kuota penuh). Gabungkan TP yang berdekatan.');
            $this->pastikanSatuSas($rombel->id, null, $data);

            $tp = Tp::create(array_merge(['masuk_sts' => false, 'is_sas' => false], $data, ['rombel_id' => $rombel->id]));
            AuditService::record('create', 'tp', $tp->id, null, $tp->toArray());

            return $tp;
        });

        return response()->json(['data' => $tp], 201);
    }

    public function update(TpRequest $request, Tp $tp): JsonResponse
    {
        $data = $request->validated();

        $tp = DB::transaction(function () use ($tp, $data) {
            $rombel = Rombel::whereKey($tp->rombel_id)->lockForUpdate()->firstOrFail();
            PenilaianAccess::pastikanRombelBisaDiubah($rombel);
            $this->pastikanSatuSas($rombel->id, $tp->id, $data);

            $sebelum = $tp->toArray();
            $tp->update($data);
            AuditService::record('update', 'tp', $tp->id, $sebelum, $tp->toArray());

            return $tp;
        });

        return response()->json(['data' => $tp]);
    }

    public function destroy(Request $request, Tp $tp): JsonResponse
    {
        $rombel = $tp->rombel;
        abort_unless($rombel && PenilaianAccess::bisaKelolaRombel($request->user(), $rombel), 403, 'Rombel ini bukan rombel yang Anda ampu.');

        DB::transaction(function () use ($tp, $rombel) {
            $rombel = Rombel::whereKey($rombel->id)->lockForUpdate()->firstOrFail();
            PenilaianAccess::pastikanRombelBisaDiubah($rombel);
            abort_if($tp->nilai()->whereNotNull('skor')->exists(), 422, 'TP sudah memiliki nilai dan tidak dapat dihapus.');

            $sebelum = $tp->toArray();
            $tp->nilai()->delete(); // hanya baris kosong (skor null)
            $tp->delete();
            AuditService::record('delete', 'tp', $sebelum['id'], $sebelum, null);
        });

        return response()->json(['message' => 'TP dihapus.']);
    }

    /** POST /tp/salin  body: dari_rombel_id, ke_rombel_id — hanya jika rombel tujuan belum punya TP. */
    public function salin(Request $request): JsonResponse
    {
        $data = $request->validate([
            'dari_rombel_id' => ['required', 'integer', 'exists:rombel,id'],
            'ke_rombel_id' => ['required', 'integer', 'exists:rombel,id', 'different:dari_rombel_id'],
        ]);
        $user = $request->user();
        $sumber = Rombel::findOrFail($data['dari_rombel_id']);
        $tujuan = Rombel::findOrFail($data['ke_rombel_id']);
        abort_unless(PenilaianAccess::bisaKelolaRombel($user, $sumber) && PenilaianAccess::bisaKelolaRombel($user, $tujuan), 403, 'Anda harus mengampu kedua rombel.');

        $hasil = DB::transaction(function () use ($sumber, $tujuan) {
            // Kunci kedua rombel dengan urutan ID tetap untuk menghindari deadlock salin silang.
            $locked = Rombel::whereIn('id', [$sumber->id, $tujuan->id])->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $tujuanLocked = $locked->get($tujuan->id);
            PenilaianAccess::pastikanRombelBisaDiubah($tujuanLocked);
            abort_if(Tp::where('rombel_id', $tujuanLocked->id)->exists(), 422, 'Rombel tujuan sudah memiliki TP. Hapus TP tujuan terlebih dahulu.');
            $tpSumber = $locked->get($sumber->id)->tp()->get();
            abort_if($tpSumber->isEmpty() || $tpSumber->count() > PenilaianAccess::MAKS_TP
                || $tpSumber->pluck('nomor_urut')->unique()->count() !== $tpSumber->count()
                || $tpSumber->contains(fn ($tp) => $tp->nomor_urut < 1 || $tp->nomor_urut > PenilaianAccess::MAKS_TP),
                422, 'Sumber salin harus memiliki 1–10 TP dengan nomor unik pada rentang 1–10.');
            $penanda = $tpSumber->where('is_sas', true);
            abort_if($penanda->count() > 1 || ($penanda->isNotEmpty() && $penanda->first()->id !== $tpSumber->last()->id),
                422, 'Penanda is_sas sumber tidak valid: hanya boleh pada satu TP terakhir. Periksa sumber salin.');

            $salin = [];
            foreach ($tpSumber as $t) {
                $salin[] = Tp::create([
                    'rombel_id' => $tujuanLocked->id,
                    'nomor_urut' => $t->nomor_urut,
                    'deskripsi' => $t->deskripsi,
                    'masuk_sts' => $t->masuk_sts,
                    'is_sas' => $t->is_sas,
                ]);
            }
            AuditService::record('salin_tp', 'tp', $tujuanLocked->id, null, ['dari_rombel_id' => $sumber->id, 'jumlah' => count($salin)]);

            return $salin;
        });

        return response()->json(['data' => $hasil], 201);
    }

    private function pastikanSatuSas(int $rombelId, ?int $kecualiId, array $data): void
    {
        $tp = Tp::where('rombel_id', $rombelId)->get();
        $lama = $tp->firstWhere('id', $kecualiId);
        $lain = $tp->reject(fn ($t) => $t->id === $kecualiId);
        $penanda = $lain->where('is_sas', true)->pluck('nomor_urut');
        if ($data['is_sas'] ?? $lama?->is_sas ?? false) {
            $penanda->push((int) $data['nomor_urut']);
        }
        abort_if($penanda->count() > 1, 422, 'Hanya satu TP yang boleh ditandai is_sas (TP terakhir).');
        $maks = max((int) $lain->max('nomor_urut'), (int) $data['nomor_urut']);
        abort_if($penanda->isNotEmpty() && (int) $penanda->first() !== $maks, 422,
            'is_sas hanya penanda TP terakhir. Perbaiki penanda lama terlebih dahulu; alokasi SAS dihitung dari urutan.');
    }
}
