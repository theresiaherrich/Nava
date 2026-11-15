<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\SiswaKelas;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SiswaKelasController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate(['semester_id' => ['sometimes', 'integer', 'exists:semester,id'], 'kelas_id' => ['sometimes', 'integer', 'exists:kelas,id']]);

        return response()->json(SiswaKelas::with(['siswa', 'kelas'])->when(isset($data['semester_id']), fn ($q) => $q->where('semester_id', $data['semester_id']))->when(isset($data['kelas_id']), fn ($q) => $q->where('kelas_id', $data['kelas_id']))->orderBy('id')->paginate(20));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate(['siswa_id' => ['required', 'integer', 'exists:siswa,id'], 'kelas_id' => ['required', 'integer', 'exists:kelas,id'], 'semester_id' => ['prohibited']]);
        $record = DB::transaction(function () use ($data) {
            $siswa = Siswa::whereKey($data['siswa_id'])->lockForUpdate()->firstOrFail();
            abort_unless($siswa->aktif, 422, 'Siswa tidak aktif.');
            $kelas = Kelas::findOrFail($data['kelas_id']);
            if (SiswaKelas::where('siswa_id', $siswa->id)->where('semester_id', $kelas->semester_id)->exists()) {
                throw ValidationException::withMessages(['siswa_id' => 'Siswa sudah memiliki kelas pada semester tersebut.']);
            }
            // Semester diturunkan dari kelas, bukan dipercaya dari input klien.
            $record = SiswaKelas::create(['siswa_id' => $siswa->id, 'kelas_id' => $kelas->id, 'semester_id' => $kelas->semester_id]);
            AuditService::record('create', 'siswa_kelas', $record->id, null, $record->toArray());

            return $record;
        });

        return response()->json(['data' => $record], 201);
    }
}
