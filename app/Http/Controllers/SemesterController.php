<?php

namespace App\Http\Controllers;

use App\Http\Requests\SemesterRequest;
use App\Models\Semester;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SemesterController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate(['q' => ['nullable', 'string', 'max:100'], 'page' => ['sometimes', 'integer', 'min:1']]);

        return response()->json(Semester::query()->when($data['q'] ?? null, fn ($q, $term) => $q->where('nama', 'like', '%'.$term.'%'))->orderBy('id')->paginate(20));
    }

    public function show(Semester $record): JsonResponse
    {
        return response()->json(['data' => $record]);
    }

    public function store(SemesterRequest $request): JsonResponse
    {
        $record = DB::transaction(function () use ($request) {
            $record = Semester::create($request->validated());
            AuditService::record('create', 'semester', $record->id, null, $record->toArray());

            return $record;
        });

        return response()->json(['data' => $record], 201);
    }

    public function update(SemesterRequest $request, Semester $record): JsonResponse
    {
        $data = $request->validated();
        abort_if($record->tahun_ajaran_id != $data['tahun_ajaran_id'], 422, 'Tahun ajaran semester tidak boleh diubah.');
        $record = DB::transaction(function () use ($record, $data) {
            $record = Semester::whereKey($record->id)->lockForUpdate()->firstOrFail();
            $before = $record->toArray();
            $record->update($data);
            AuditService::record('update', 'semester', $record->id, $before, $record->toArray());

            return $record;
        });

        return response()->json(['data' => $record]);
    }
}
