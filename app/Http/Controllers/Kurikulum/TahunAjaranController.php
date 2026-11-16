<?php

namespace App\Http\Controllers\Kurikulum;

use App\Http\Controllers\Controller;
use App\Http\Requests\Kurikulum\TahunAjaranRequest;
use App\Models\Semester;
use App\Models\TahunAjaran;
use App\Services\TahunAjaranService;
use Illuminate\Http\JsonResponse;

class TahunAjaranController extends Controller
{
    public function __construct(private TahunAjaranService $ta) {}

    public function index(): JsonResponse
    {
        return $this->ok(TahunAjaran::with('semester')->orderByDesc('nama')->get());
    }

    public function store(TahunAjaranRequest $request): JsonResponse
    {
        return $this->created($this->ta->buat($request->string('nama')), 'Tahun ajaran dibuat beserta semester Ganjil dan Genap.');
    }

    public function aktifkanSemester(Semester $semester): JsonResponse
    {
        return $this->ok($this->ta->aktifkanSemester($semester), 'Semester aktif diganti.');
    }
}
