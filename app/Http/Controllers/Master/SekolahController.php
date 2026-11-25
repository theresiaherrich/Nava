<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\SekolahRequest;
use App\Models\Agama;
use App\Models\Sekolah;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/** Identitas sekolah (satu mitra, id 1) dan data referensi. */
class SekolahController extends Controller
{
    public function __construct(private AuditService $audit) {}

    public function show(): JsonResponse
    {
        return $this->ok(Sekolah::find(1));
    }

    public function update(SekolahRequest $request): JsonResponse
    {
        $sekolah = DB::transaction(function () use ($request) {
            $lama = Sekolah::find(1);
            $s = Sekolah::updateOrCreate(['id_sekolah' => 1], $request->validated());
            $this->audit->catat('ubah_sekolah', 'sekolah', 1, $lama?->only(array_keys($request->validated())), $request->validated());

            return $s;
        });

        return $this->ok($sekolah, 'Identitas sekolah disimpan.');
    }

    public function agama(): JsonResponse
    {
        return $this->ok(Agama::orderBy('id_agama')->get());
    }
}
