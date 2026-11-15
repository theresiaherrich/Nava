<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Mapel;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MapelController extends Controller
{
    public function __construct(private AuditService $audit) {}

    public function index(Request $request): JsonResponse
    {
        $query = Mapel::query()->orderBy('nama');

        if ($q = trim((string) $request->query('q', ''))) {
            $query->where(fn ($w) => $w->where('nama', 'like', "%{$q}%")->orWhere('kode', 'like', "%{$q}%"));
        }

        return response()->json($query->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'kode' => ['required', 'string', 'max:30', 'unique:mapel,kode'],
            'nama' => ['required', 'string', 'max:120'],
            'kelompok' => ['nullable', Rule::in(['umum', 'agama'])],
        ]);

        $mapel = Mapel::create($data + ['kelompok' => $data['kelompok'] ?? 'umum', 'aktif' => true]);

        $this->audit->catat('buat_mapel', 'mapel', $mapel->id_mapel, null, null, $mapel->only(['kode', 'nama', 'kelompok']));

        return response()->json($mapel, 201);
    }

    public function update(Request $request, Mapel $mapel): JsonResponse
    {
        $data = $request->validate([
            'kode' => ['sometimes', 'string', 'max:30', Rule::unique('mapel', 'kode')->ignore($mapel->id_mapel, 'id_mapel')],
            'nama' => ['sometimes', 'string', 'max:120'],
            'kelompok' => ['sometimes', Rule::in(['umum', 'agama'])],
            'aktif' => ['sometimes', 'boolean'],
        ]);

        $sebelum = $mapel->only(array_keys($data));
        $mapel->update($data);

        $this->audit->catat('ubah_mapel', 'mapel', $mapel->id_mapel, null, $sebelum, $mapel->only(array_keys($data)));

        return response()->json($mapel);
    }
}
