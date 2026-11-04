<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\WaliKelas;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WaliKelasController extends Controller
{
    /** PUT /kelas/{record}/wali-kelas  body: guru_id */
    public function set(Request $request, Kelas $record): JsonResponse
    {
        $data = $request->validate(['guru_id' => ['required', 'integer', 'exists:guru,id']]);

        $wali = DB::transaction(function () use ($record, $data) {
            $sebelum = WaliKelas::where('kelas_id', $record->id)->first()?->toArray();
            $wali = WaliKelas::updateOrCreate(['kelas_id' => $record->id], ['guru_id' => $data['guru_id']]);
            AuditService::record('set_wali_kelas', 'wali_kelas', $wali->id, $sebelum, $wali->toArray());

            return $wali;
        });

        return response()->json(['data' => $wali]);
    }
}
