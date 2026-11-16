<?php

namespace App\Http\Controllers;

use App\Models\Sekolah;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SekolahController extends Controller
{
    public function show(): JsonResponse
    {
        return response()->json(['data' => Sekolah::find(1)]);
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate(['npsn' => ['required', 'digits:8'], 'nama' => ['required', 'string', 'max:255'], 'alamat' => ['required', 'string', 'max:2000'], 'nama_kepala_sekolah' => ['required', 'string', 'max:255']]);
        $record = DB::transaction(function () use ($data) {
            // Aplikasi ini untuk satu mitra sekolah: identitas selalu pada id 1.
            $before = Sekolah::find(1)?->toArray();
            $record = Sekolah::updateOrCreate(['id' => 1], $data);
            AuditService::record('update', 'sekolah', 1, $before, $record->toArray());

            return $record;
        });

        return response()->json(['data' => $record]);
    }
}
