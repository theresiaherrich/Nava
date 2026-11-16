<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Sekolah;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SekolahController extends Controller
{
    public function __construct(private AuditService $audit) {}

    public function show(): JsonResponse
    {
        return response()->json(Sekolah::first());
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:120'],
            'alamat' => ['nullable', 'string'],
            'telepon' => ['nullable', 'string', 'max:25'],
            'email' => ['nullable', 'email', 'max:100'],
            'npsn' => ['nullable', 'string', 'max:10'],
        ]);

        $sekolah = Sekolah::first() ?? new Sekolah();
        $sebelum = $sekolah->exists ? $sekolah->only(array_keys($data)) : null;

        $sekolah->fill($data)->save();

        $this->audit->catat('ubah_sekolah', 'sekolah', $sekolah->id_sekolah, null, $sebelum, $sekolah->only(array_keys($data)));

        return response()->json($sekolah);
    }
}
