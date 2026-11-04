<?php

namespace App\Http\Controllers;

use App\Http\Requests\RombelRequest;
use App\Models\Rombel;
use App\Models\SiswaKelas;
use App\Models\SiswaRombel;
use App\Services\AuditService;
use App\Services\PenilaianAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RombelController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate(['semester_id' => ['required', 'integer', 'exists:semester,id']]);
        $user = $request->user();

        $query = Rombel::with(['mapel', 'gurus'])->where('semester_id', $data['semester_id'])->orderBy('nama');
        if (! $user->hasAnyRole(['admin', 'kurikulum'])) {
            // Guru hanya melihat rombel yang ia ampu.
            $guruId = PenilaianAccess::guruId($user) ?? 0;
            $query->whereHas('gurus', fn ($q) => $q->where('guru.id', $guruId));
        }

        return response()->json($query->paginate(20));
    }

    public function show(Request $request, Rombel $record): JsonResponse
    {
        abort_unless(PenilaianAccess::bisaKelolaRombel($request->user(), $record), 403, 'Rombel ini bukan rombel yang Anda ampu.');

        return response()->json(['data' => $record->load(['mapel', 'gurus'])]);
    }

    public function store(RombelRequest $request): JsonResponse
    {
        $record = DB::transaction(function () use ($request) {
            $record = Rombel::create($request->validated());
            AuditService::record('create', 'rombel', $record->id, null, $record->toArray());

            return $record;
        });

        return response()->json(['data' => $record], 201);
    }

    public function update(RombelRequest $request, Rombel $record): JsonResponse
    {
        $data = $request->validated();

        $record = DB::transaction(function () use ($record, $data) {
            $record = Rombel::whereKey($record->id)->lockForUpdate()->firstOrFail();
            $pindahKonteks = (int) $data['semester_id'] !== (int) $record->semester_id || (int) $data['mapel_id'] !== (int) $record->mapel_id;
            abort_if($pindahKonteks && ($record->peserta()->exists() || $record->tp()->exists()), 422, 'Semester/mapel rombel tidak dapat diubah karena sudah memiliki peserta atau TP.');

            $sebelum = $record->toArray();
            $record->update($data);
            AuditService::record('update', 'rombel', $record->id, $sebelum, $record->toArray());

            return $record;
        });

        return response()->json(['data' => $record]);
    }

    /** PUT /rombel/{record}/guru  body: guru_ids[] — mengganti seluruh pengampu. */
    public function setGuru(Request $request, Rombel $record): JsonResponse
    {
        $data = $request->validate([
            'guru_ids' => ['present', 'array'],
            'guru_ids.*' => ['integer', 'distinct', 'exists:guru,id'],
        ]);

        DB::transaction(function () use ($record, $data) {
            $sebelum = $record->gurus()->pluck('guru.id')->map(fn ($id) => (int) $id)->all();
            $record->gurus()->sync($data['guru_ids']);
            AuditService::record('update_pengampu', 'rombel', $record->id, ['guru_ids' => $sebelum], ['guru_ids' => $data['guru_ids']]);
        });

        return response()->json(['data' => $record->load('gurus')]);
    }

    /** POST /rombel/{record}/peserta  body: siswa_kelas_ids[] — dari kelas asal yang berbeda boleh. */
    public function tambahPeserta(Request $request, Rombel $record): JsonResponse
    {
        $data = $request->validate([
            'siswa_kelas_ids' => ['required', 'array', 'min:1', 'max:200'],
            'siswa_kelas_ids.*' => ['integer', 'distinct', 'exists:siswa_kelas,id'],
        ]);

        $ditambahkan = DB::transaction(function () use ($record, $data) {
            $rombel = Rombel::whereKey($record->id)->lockForUpdate()->firstOrFail();
            $sudah = $rombel->peserta()->pluck('siswa_kelas_id')->map(fn ($id) => (int) $id)->all();
            $items = SiswaKelas::with('siswa')->whereIn('id', $data['siswa_kelas_ids'])->get();

            foreach ($items as $sk) {
                if ((int) $sk->semester_id !== (int) $rombel->semester_id) {
                    throw ValidationException::withMessages(['siswa_kelas_ids' => "Penempatan kelas {$sk->id} berasal dari semester berbeda dengan rombel."]);
                }
                if (! $sk->siswa?->aktif) {
                    throw ValidationException::withMessages(['siswa_kelas_ids' => "Siswa pada penempatan {$sk->id} tidak aktif."]);
                }
            }

            $hasil = [];
            foreach ($items as $sk) {
                if (in_array((int) $sk->id, $sudah, true)) {
                    continue; // idempoten: peserta yang sudah ada tidak dibuat ganda
                }
                $peserta = SiswaRombel::create(['rombel_id' => $rombel->id, 'siswa_kelas_id' => $sk->id]);
                AuditService::record('create', 'siswa_rombel', $peserta->id, null, $peserta->toArray());
                $hasil[] = $peserta;
            }

            return $hasil;
        });

        return response()->json(['data' => $ditambahkan], 201);
    }
}
