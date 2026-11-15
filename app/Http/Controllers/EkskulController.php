<?php

namespace App\Http\Controllers;

use App\Models\Ekskul;
use App\Models\Predikat;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Bagian 4.7: master ekskul (kesiswaan dan admin). */
class EkskulController extends Controller
{
    /** GET /ekskul?q=&semester_id=  (semester_id opsional: jumlah peserta dihitung pada semester itu) */
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'semester_id' => ['nullable', 'integer', 'exists:semester,id'],
        ]);

        $ekskul = Ekskul::query()
            ->withCount(['peserta' => fn ($q) => $q->when($data['semester_id'] ?? null, fn ($s, $id) => $s->where('id_semester', $id))])
            ->when($data['q'] ?? null, fn ($q, $kata) => $q->where('nama', 'like', '%'.$kata.'%'))
            ->orderBy('nama')
            ->get()
            ->map(fn (Ekskul $e) => ['id' => $e->id_ekskul, 'nama' => $e->nama, 'jumlah_peserta' => $e->peserta_count]);

        return response()->json([
            'data' => $ekskul,
            // Daftar predikat aktif untuk pilihan penilaian (rentang nilai bisa null sampai dikonfirmasi Kesiswaan).
            'predikat' => Predikat::where('aktif', true)->orderBy('id_predikat')
                ->get()->map(fn (Predikat $p) => ['id' => $p->id_predikat, 'predikat' => $p->predikat, 'keterangan' => $p->keterangan, 'nilai_min' => $p->nilai_min, 'nilai_max' => $p->nilai_max]),
        ]);
    }

    /** POST /ekskul  body: nama */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate(['nama' => ['required', 'string', 'min:2', 'max:120', 'unique:ekskul,nama']]);
        $data['nama'] = trim($data['nama']);

        $ekskul = DB::transaction(function () use ($data, $request) {
            $ekskul = Ekskul::create($data);
            AuditService::record('buat_ekskul', 'ekskul', $ekskul->id_ekskul, null, $ekskul->toArray(), null, $request->user()->id);

            return $ekskul;
        });

        return response()->json(['data' => ['id' => $ekskul->id_ekskul, 'nama' => $ekskul->nama, 'jumlah_peserta' => 0]], 201);
    }
}
