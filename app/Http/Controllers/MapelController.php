<?php

namespace App\Http\Controllers;

use App\Http\Requests\MapelRequest;
use App\Models\Mapel;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MapelController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate(['q' => ['nullable', 'string', 'max:100'], 'page' => ['sometimes', 'integer', 'min:1']]);

        return response()->json(Mapel::query()->when($data['q'] ?? null, fn ($q, $term) => $q->where('nama', 'like', '%'.$term.'%'))->orderBy('id')->paginate(20));
    }

    public function show(Mapel $record): JsonResponse
    {
        return response()->json(['data' => $record]);
    }

    public function store(MapelRequest $request): JsonResponse
    {
        $record = DB::transaction(function () use ($request) {
            $record = Mapel::create($request->validated());
            AuditService::record('create', 'mapel', $record->id, null, $record->toArray());

            return $record;
        });

        return response()->json(['data' => $record], 201);
    }

    public function update(MapelRequest $request, Mapel $record): JsonResponse
    {
        $data = $request->validated();

        $record = DB::transaction(function () use ($record, $data) {
            $record = Mapel::whereKey($record->id)->lockForUpdate()->firstOrFail();
            $before = $record->toArray();
            $record->update($data);
            AuditService::record('update', 'mapel', $record->id, $before, $record->toArray());

            return $record;
        });

        return response()->json(['data' => $record]);
    }
}
