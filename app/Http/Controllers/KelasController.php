<?php

namespace App\Http\Controllers;

use App\Http\Requests\KelasRequest;
use App\Models\Kelas;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KelasController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate(['q' => ['nullable', 'string', 'max:100'], 'page' => ['sometimes', 'integer', 'min:1']]);

        return response()->json(Kelas::query()->when($data['q'] ?? null, fn ($q, $term) => $q->where('nama', 'like', '%'.$term.'%'))->orderBy('id')->paginate(20));
    }

    public function show(Kelas $record): JsonResponse
    {
        return response()->json(['data' => $record]);
    }

    public function store(KelasRequest $request): JsonResponse
    {
        $record = DB::transaction(function () use ($request) {
            $record = Kelas::create($request->validated());
            AuditService::record('create', 'kelas', $record->id, null, $record->toArray());

            return $record;
        });

        return response()->json(['data' => $record], 201);
    }

    public function update(KelasRequest $request, Kelas $record): JsonResponse
    {
        $data = $request->validated();
        abort_if($record->semester_id != $data['semester_id'], 422, 'Semester kelas tidak boleh diubah. Buat kelas baru untuk semester berikutnya.');
        $record = DB::transaction(function () use ($record, $data) {
            $record = Kelas::whereKey($record->id)->lockForUpdate()->firstOrFail();
            $before = $record->toArray();
            $record->update($data);
            AuditService::record('update', 'kelas', $record->id, $before, $record->toArray());

            return $record;
        });

        return response()->json(['data' => $record]);
    }
}
