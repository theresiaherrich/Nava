<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Semester;
use App\Models\TahunAjaran;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TahunAjaranController extends Controller
{
    public function __construct(private AuditService $audit) {}

    public function index(): JsonResponse
    {
        return response()->json([
            'aktif' => Semester::with('tahunAjaran')->where('aktif', true)->first(),
            'tahun_ajaran' => TahunAjaran::with('semester')->orderByDesc('nama')->get(),
        ]);
    }

    /** Membuat tahun ajaran beserta semester GASAL dan GENAP. */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'regex:/^\d{4}\/\d{4}$/', 'unique:tahun_ajaran,nama'],
        ]);

        [$awal, $akhir] = explode('/', $data['nama']);

        if ((int) $akhir !== (int) $awal + 1) {
            throw ValidationException::withMessages(['nama' => 'Tahun kedua harus tahun pertama + 1, mis. 2026/2027.']);
        }

        $tahun = DB::transaction(function () use ($data) {
            $tahun = TahunAjaran::create(['nama' => $data['nama'], 'aktif' => false]);

            foreach (['GASAL', 'GENAP'] as $nama) {
                Semester::create(['id_tahun_ajaran' => $tahun->id_tahun_ajaran, 'nama' => $nama, 'aktif' => false]);
            }

            $this->audit->catat('buat_tahun_ajaran', 'tahun_ajaran', $tahun->id_tahun_ajaran, null, null, ['nama' => $tahun->nama]);

            return $tahun;
        });

        return response()->json($tahun->load('semester'), 201);
    }

    public function aktifkan(TahunAjaran $tahunAjaran): JsonResponse
    {
        DB::transaction(function () use ($tahunAjaran) {
            TahunAjaran::query()->update(['aktif' => false]);
            Semester::query()->update(['aktif' => false]);

            $tahunAjaran->update(['aktif' => true]);

            $this->audit->catat('aktifkan_tahun_ajaran', 'tahun_ajaran', $tahunAjaran->id_tahun_ajaran, null, null, ['aktif' => true]);
        });

        return response()->json(['message' => "Tahun ajaran {$tahunAjaran->nama} diaktifkan."]);
    }

    /** Dipakai dropdown "2026/2027 · Ganjil" di header. */
    public function aktifkanSemester(Semester $semester): JsonResponse
    {
        DB::transaction(function () use ($semester) {
            TahunAjaran::query()->update(['aktif' => false]);
            Semester::query()->update(['aktif' => false]);

            $semester->update(['aktif' => true]);
            TahunAjaran::where('id_tahun_ajaran', $semester->id_tahun_ajaran)->update(['aktif' => true]);

            $this->audit->catat('aktifkan_semester', 'semester', $semester->id_semester, null, null, ['aktif' => true]);
        });

        return response()->json(['message' => 'Semester aktif diperbarui.']);
    }
}
