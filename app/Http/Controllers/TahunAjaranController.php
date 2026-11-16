<?php

namespace App\Http\Controllers;

use App\Http\Requests\TahunAjaranRequest;
use App\Models\TahunAjaran;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TahunAjaranController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate(['q' => ['nullable', 'string', 'max:100'], 'page' => ['sometimes', 'integer', 'min:1']]);

        return response()->json(TahunAjaran::query()->when($data['q'] ?? null, fn ($q, $term) => $q->where('nama', 'like', '%'.$term.'%'))->orderBy('id')->paginate(20));
    }

    public function show(TahunAjaran $record): JsonResponse
    {
        return response()->json(['data' => $record]);
    }

    public function store(TahunAjaranRequest $request): JsonResponse
    {
        $record = DB::transaction(function () use ($request) {
            $record = TahunAjaran::create($request->validated());
            AuditService::record('create', 'tahun_ajaran', $record->id, null, $record->toArray());

            return $record;
        });

        return response()->json(['data' => $record], 201);
    }

    public function update(TahunAjaranRequest $request, TahunAjaran $record): JsonResponse
    {
        $data = $request->validated();

        $record = DB::transaction(function () use ($record, $data) {
            $record = TahunAjaran::whereKey($record->id)->lockForUpdate()->firstOrFail();
            $before = $record->toArray();
            $record->update($data);
            AuditService::record('update', 'tahun_ajaran', $record->id, $before, $record->toArray());

            return $record;
        });

        return response()->json(['data' => $record]);
    }
}
