<?php

namespace App\Http\Controllers\Kurikulum;

use App\Http\Controllers\Controller;
use App\Http\Requests\Kurikulum\WaliKelasRequest;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\WaliKelas;
use App\Services\WaliKelasService;
use App\Support\AcademicAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WaliKelasController extends Controller
{
    public function __construct(private WaliKelasService $wali) {}

    public function index(Request $request): JsonResponse
    {
        $semester = AcademicAccess::semester($request->integer('id_semester') ?: null);

        $kelas = Kelas::where('id_tahun_ajaran', $semester->id_tahun_ajaran)
            ->with(['wali' => fn ($q) => $q->where('id_semester', $semester->id_semester)->with('guru:id_guru,nama,nip')])
            ->orderByRaw("CASE tingkat WHEN 'X' THEN 1 WHEN 'XI' THEN 2 ELSE 3 END")->orderBy('nama')->get();

        return $this->ok($kelas->map(fn (Kelas $k) => $k->only(['id_kelas', 'nama', 'tingkat']) + [
            'id_wali_kelas' => $k->wali->first()?->id_wali_kelas,
            'guru' => $k->wali->first()?->guru,
        ]));
    }

    public function store(WaliKelasRequest $request, Kelas $kelas): JsonResponse
    {
        $semester = AcademicAccess::semester($request->integer('id_semester') ?: null);
        $wali = $this->wali->tetapkan($kelas, Guru::findOrFail($request->integer('id_guru')), $semester);

        return $this->ok($wali, 'Wali kelas ditetapkan. Peran Wali Kelas diberikan otomatis.');
    }

    public function destroy(WaliKelas $waliKelas): JsonResponse
    {
        $this->wali->hapus($waliKelas);

        return $this->ok(null, 'Penetapan wali kelas dihapus.');
    }
}
