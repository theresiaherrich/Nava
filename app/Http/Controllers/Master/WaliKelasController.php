<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Semester;
use App\Models\WaliKelas;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class WaliKelasController extends Controller
{
    public function __construct(private AuditService $audit) {}

    /** Penetapan wali kelas (Kurikulum). Guru harus sudah memiliki peran WALI_KELAS. */
    public function store(Request $request, Kelas $kelas): JsonResponse
    {
        $data = $request->validate([
            'id_guru' => ['required', 'exists:guru,id_guru'],
            'id_semester' => ['required', 'exists:semester,id_semester'],
        ]);

        $guru = Guru::with('user')->findOrFail($data['id_guru']);
        $semester = Semester::findOrFail($data['id_semester']);

        if (! $guru->user || ! $guru->user->hasRole('WALI_KELAS')) {
            throw ValidationException::withMessages(['id_guru' => 'Tambahkan peran Wali Kelas pada akun guru ini terlebih dahulu.']);
        }

        $this->pastikanSatuTahunAjaran($kelas, $semester);

        $sebelum = WaliKelas::where('id_kelas', $kelas->id_kelas)->where('id_semester', $semester->id_semester)->value('id_guru');

        $wali = WaliKelas::updateOrCreate(
            ['id_kelas' => $kelas->id_kelas, 'id_semester' => $semester->id_semester],
            ['id_guru' => $guru->id_guru],
        );

        $this->audit->catat('tetapkan_wali_kelas', 'kelas', $kelas->id_kelas, null,
            ['id_guru' => $sebelum], ['id_guru' => $guru->id_guru, 'id_semester' => $semester->id_semester]);

        return response()->json($wali->load('guru:id_guru,nama'), 201);
    }

    public function destroy(WaliKelas $waliKelas): JsonResponse
    {
        $sebelum = ['id_guru' => $waliKelas->id_guru, 'id_kelas' => $waliKelas->id_kelas];
        $waliKelas->delete();

        $this->audit->catat('lepas_wali_kelas', 'kelas', $sebelum['id_kelas'], null, $sebelum, null);

        return response()->json(['message' => 'Penetapan wali kelas dilepas.']);
    }
}
