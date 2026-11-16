<?php

namespace App\Http\Controllers;

use App\Http\Requests\SiswaRequest;
use App\Models\Siswa;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SiswaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate(['q' => ['nullable', 'string', 'max:100'], 'page' => ['sometimes', 'integer', 'min:1']]);

        return response()->json(Siswa::query()->when($data['q'] ?? null, fn ($q, $term) => $q->where('nama', 'like', '%'.$term.'%'))->orderBy('id')->paginate(20));
    }

    public function show(Siswa $record): JsonResponse
    {
        return response()->json(['data' => $record]);
    }

    public function store(SiswaRequest $request): JsonResponse
    {
        $record = DB::transaction(function () use ($request) {
            $record = Siswa::create($request->validated());
            AuditService::record('create', 'siswa', $record->id, null, $record->toArray());

            return $record;
        });

        return response()->json(['data' => $record], 201);
    }

    public function update(SiswaRequest $request, Siswa $record): JsonResponse
    {
        $data = $request->validated();

        $record = DB::transaction(function () use ($record, $data) {
            $record = Siswa::whereKey($record->id)->lockForUpdate()->firstOrFail();
            $before = $record->toArray();
            $record->update($data);
            AuditService::record('update', 'siswa', $record->id, $before, $record->toArray());

            return $record;
        });

        return response()->json(['data' => $record]);
    }
}
