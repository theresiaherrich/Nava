<?php

namespace App\Http\Controllers\Kurikulum;

use App\Http\Controllers\Controller;
use App\Http\Requests\Kurikulum\MapelRequest;
use App\Models\Mapel;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MapelController extends Controller
{
    public function __construct(private AuditService $audit) {}

    public function index(Request $request): JsonResponse
    {
        $request->validate(['q' => ['nullable', 'string', 'max:100'], 'aktif' => ['nullable', 'boolean']]);

        return $this->ok(Mapel::with('agama:id_agama,nama')
            ->when($request->q, fn ($q, $s) => $q->where(fn ($w) => $w->where('nama', 'like', "%{$s}%")->orWhere('kode', 'like', "%{$s}%")))
            ->when($request->has('aktif'), fn ($q) => $q->where('aktif', $request->boolean('aktif')))
            ->orderBy('urutan_rapor')->orderBy('nama')->get());
    }

    public function store(MapelRequest $request): JsonResponse
    {
        $mapel = DB::transaction(function () use ($request) {
            $m = Mapel::create($request->validated());
            $this->audit->catat('buat_mapel', 'mapel', $m->id_mapel, null, $request->validated());

            return $m;
        });

        return $this->created($mapel->fresh('agama'), 'Mata pelajaran ditambahkan.');
    }

    public function update(MapelRequest $request, Mapel $mapel): JsonResponse
    {
        DB::transaction(function () use ($request, $mapel) {
            $sebelum = $mapel->only(array_keys($request->validated()));
            $mapel->update($request->validated());
            $this->audit->catat('ubah_mapel', 'mapel', $mapel->id_mapel, $sebelum, $request->validated());
        });

        return $this->ok($mapel->fresh('agama'), 'Mata pelajaran diperbarui.');
    }
}
