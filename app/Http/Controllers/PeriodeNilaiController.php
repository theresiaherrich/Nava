<?php

namespace App\Http\Controllers;

use App\Models\PeriodeNilai;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PeriodeNilaiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate(['semester_id' => ['required', 'integer', 'exists:semester,id']]);

        return response()->json(['data' => PeriodeNilai::where('semester_id', $data['semester_id'])->orderBy('jenis')->orderBy('tingkat')->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validatePeriode($request);

        $record = DB::transaction(function () use ($data) {
            $record = PeriodeNilai::create($data);
            AuditService::record('create', 'periode_nilai', $record->id, null, $record->toArray());

            return $record;
        });

        return response()->json(['data' => $record], 201);
    }

    public function update(Request $request, PeriodeNilai $record): JsonResponse
    {
        $data = $this->validatePeriode($request, $record->id);

        $record = DB::transaction(function () use ($record, $data) {
            $record = PeriodeNilai::whereKey($record->id)->lockForUpdate()->firstOrFail();
            abort_if($record->dikunci, 422, 'Periode yang sudah dikunci tidak dapat diubah.');
            $sebelum = $record->toArray();
            $record->update($data);
            AuditService::record('update', 'periode_nilai', $record->id, $sebelum, $record->toArray());

            return $record;
        });

        return response()->json(['data' => $record]);
    }

    /** POST /periode-nilai/{record}/kunci — membekukan seluruh periode (langkah Kurikulum). */
    public function kunci(Request $request, PeriodeNilai $record): JsonResponse
    {
        $record = DB::transaction(function () use ($request, $record) {
            $record = PeriodeNilai::whereKey($record->id)->lockForUpdate()->firstOrFail();
            $sebelum = $record->toArray();
            $record->update(['dikunci' => true]);
            AuditService::record('kunci_periode', 'periode_nilai', $record->id, $sebelum, $record->toArray(), null, $request->user()->id);

            return $record;
        });

        return response()->json(['data' => $record]);
    }

    private function validatePeriode(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'semester_id' => ['required', 'integer', 'exists:semester,id'],
            'jenis' => ['required', Rule::in(['STS', 'SAS']), Rule::unique('periode_nilai', 'jenis')
                ->where(fn ($q) => $q->where('semester_id', $request->input('semester_id'))->where('tingkat', $request->input('tingkat')))
                ->ignore($ignoreId)],
            'tingkat' => ['required', 'integer', Rule::in([10, 11, 12])],
            'tanggal_buka' => ['required', 'date'],
            'tanggal_tutup' => ['required', 'date', 'after_or_equal:tanggal_buka'],
            'dikunci' => ['prohibited'],
        ]);
    }
}
