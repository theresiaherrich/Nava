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
use Illuminate\Validation\ValidationException;

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
        abort_unless((int) $periode->semester_id === (int) $record->semester_id, 422, 'Semester periode tidak sesuai dengan rombel.');
        $tp = $record->tp()->get();
        $peserta = SiswaRombel::with(['siswaKelas.siswa', 'siswaKelas.kelas'])
            ->where('rombel_id', $record->id)
            ->whereHas('siswaKelas.kelas', fn ($q) => $q->where('tingkat', $periode->tingkat)->where('semester_id', $periode->semester_id))
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

        $data = $this->validasiItems($request);

        $jumlah = DB::transaction(function () use ($record, $data, $user) {
            $periode = PeriodeNilai::whereKey($data['periode_id'])->lockForUpdate()->firstOrFail();
            $this->pastikanDapatDiisi($record, $periode, $data['items']);

            foreach ($data['items'] as $item) {
                NilaiTp::updateOrCreate(
                    ['tp_id' => $item['tp_id'], 'siswa_rombel_id' => $item['siswa_rombel_id']],
                    ['skor' => $item['skor'], 'diubah_oleh' => $user->id],
                );
            }

            AuditService::record('simpan_nilai', 'nilai_tp', $periode->id, null, ['rombel_id' => $record->id, 'jumlah_sel' => count($data['items'])], null, $user->id);

            return count($data['items']);
        });

        return response()->json(['data' => ['tersimpan' => $jumlah, 'waktu' => now()->toIso8601String()]]);
    }

    /** Pratinjau menerima pemetaan sel yang sama dengan PUT; hanya membaca database. */
    public function pratinjauTempel(Request $request, Rombel $record): JsonResponse
    {
        abort_unless(PenilaianAccess::bisaInputNilai($request->user(), $record), 403, 'Hanya guru pengampu rombel ini yang dapat mengisi nilai.');
        $data = $this->validasiItems($request);
        $periode = PeriodeNilai::findOrFail($data['periode_id']);
        $this->pastikanDapatDiisi($record, $periode, $data['items']);

        return response()->json(['data' => $data + ['jumlah_sel' => count($data['items'])]]);
    }

    private function validasiItems(Request $request): array
    {
        $data = $request->validate([
            'periode_id' => ['required', 'integer', 'exists:periode_nilai,id'],
            'items' => ['required', 'array', 'list', 'min:1', 'max:1000'],
            'items.*.siswa_rombel_id' => ['required', 'integer'],
            'items.*.tp_id' => ['required', 'integer'],
            'items.*' => ['required', 'array:siswa_rombel_id,tp_id,skor'],
            'items.*.skor' => ['present', 'nullable', 'numeric', 'between:0,100', 'decimal:0,2'],
        ]);

        $pasangan = [];
        foreach ($data['items'] as $index => &$item) {
            $item['siswa_rombel_id'] = (int) $item['siswa_rombel_id'];
            $item['tp_id'] = (int) $item['tp_id'];
            $key = $item['siswa_rombel_id'].':'.$item['tp_id'];
            if (isset($pasangan[$key])) {
                throw ValidationException::withMessages(["items.{$index}.tp_id" => 'Pasangan siswa dan TP tidak boleh berulang.']);
            }
            $pasangan[$key] = true;
        }
        unset($item);

        return $data;
    }

    private function pastikanDapatDiisi(Rombel $record, PeriodeNilai $periode, array $items): void
    {
        abort_unless((int) $periode->semester_id === (int) $record->semester_id, 422, 'Semester periode tidak sesuai dengan rombel.');
        abort_unless($periode->terbuka(), 422, 'Periode penilaian sedang tertutup atau sudah dikunci.');

        $sel = collect($items);
        $tpMap = $record->tp()->whereIn('id', $sel->pluck('tp_id')->unique()->values())->get()->keyBy('id');
        $pesertaMap = SiswaRombel::with('siswaKelas.kelas')
            ->where('rombel_id', $record->id)
            ->whereIn('id', $sel->pluck('siswa_rombel_id')->unique()->values())
            ->get()->keyBy('id');

        $kelasIds = [];
        $errors = [];
        foreach ($items as $index => $item) {
            $tp = $tpMap->get($item['tp_id']);
            $peserta = $pesertaMap->get($item['siswa_rombel_id']);
            if ($tp === null) {
                $errors["items.{$index}.tp_id"] = 'TP tidak ditemukan dalam rombel ini.';
            }
            if ($peserta === null) {
                $errors["items.{$index}.siswa_rombel_id"] = 'Peserta tidak ditemukan dalam rombel ini.';
            }
        }
        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        foreach ($items as $item) {
            $tp = $tpMap->get($item['tp_id']);
            $peserta = $pesertaMap->get($item['siswa_rombel_id']);
            $penempatan = $peserta->siswaKelas;
            abort_unless($penempatan && $penempatan->kelas
                && (int) $penempatan->semester_id === (int) $periode->semester_id
                && (int) $penempatan->kelas->semester_id === (int) $periode->semester_id
                && (int) $penempatan->kelas->tingkat === (int) $periode->tingkat,
                422, 'Semester atau tingkat peserta tidak sesuai dengan periode.');
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
    }
}
