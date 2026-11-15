<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\AcademicAccess as Access;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RombelController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['semester_id' => 'nullable|integer|exists:semester,id_semester']);
        $q = DB::table('rombel as r')->join('mapel as m', 'm.id_mapel', '=', 'r.id_mapel')
            ->select('r.*', 'm.nama as nama_mapel');
        if ($request->filled('semester_id')) $q->where('r.id_semester', $request->integer('semester_id'));
        if (!in_array(Access::role(), ['ADMIN', 'KURIKULUM'], true)) {
            $guru = Access::teacherId();
            abort_unless($guru !== null, 403);
            $q->join('guru_rombel as gr', 'gr.id_rombel', '=', 'r.id_rombel')->where('gr.id_guru', $guru);
        }
        return response()->json(['data' => $q->orderBy('r.id_rombel')->get()]);
    }

    public function store(Request $request)
    {
        Access::manage();
        $v = $request->validate([
            'id_semester' => 'required|integer|exists:semester,id_semester',
            'id_mapel' => 'required|integer|exists:mapel,id_mapel',
            'nama' => 'required|string|max:80',
            'tingkat' => 'required|in:X,XI,XII',
            'jam' => 'nullable|string|max:50', 'kelompok_rapor' => 'nullable|string|max:80',
            'metode_nilai' => 'nullable|string|max:80',
        ]);
        $id = DB::table('rombel')->insertGetId($v);
        return response()->json(['message' => 'Rombel berhasil dibuat', 'id_rombel' => $id], 201);
    }

    public function show(int $rombel)
    {
        Access::canReadRombel($rombel);
        $data = DB::table('rombel')->where('id_rombel', $rombel)->first();
        abort_unless($data, 404);
        return response()->json(['data' => $data]);
    }

    public function update(Request $request, int $rombel)
    {
        Access::manage();
        abort_unless(DB::table('rombel')->where('id_rombel', $rombel)->exists(), 404);
        Access::editable($rombel);
        $v = $request->validate(['nama' => 'sometimes|required|string|max:80', 'jam' => 'nullable|string|max:50',
            'kelompok_rapor' => 'nullable|string|max:80', 'metode_nilai' => 'nullable|string|max:80']);
        DB::table('rombel')->where('id_rombel', $rombel)->update($v);
        return response()->json(['message' => 'Rombel diperbarui']);
    }

    public function setGuru(Request $request, int $rombel)
    {
        Access::manage(); Access::editable($rombel);
        $v = $request->validate(['id_guru' => 'required|integer|exists:guru,id_guru']);
        DB::table('guru_rombel')->updateOrInsert(['id_rombel' => $rombel, 'id_guru' => $v['id_guru']], []);
        return response()->json(['message' => 'Pengampu berhasil ditetapkan']);
    }

    public function addPeserta(Request $request, int $rombel)
    {
        Access::manage(); Access::editable($rombel);
        $v = $request->validate(['id_riwayat_kelas' => 'required|integer|exists:riwayat_kelas,id_riwayat_kelas']);
        $r = DB::table('rombel')->where('id_rombel', $rombel)->first();
        $rk = DB::table('riwayat_kelas')->where('id_riwayat_kelas', $v['id_riwayat_kelas'])->first();
        abort_unless((int)$rk->id_semester === (int)$r->id_semester, 422, 'Semester siswa dan rombel berbeda.');
        DB::table('siswa_rombel')->updateOrInsert(
            ['id_rombel' => $rombel, 'id_riwayat_kelas' => $rk->id_riwayat_kelas], ['id_mapel' => $r->id_mapel]
        );
        return response()->json(['message' => 'Peserta berhasil didaftarkan']);
    }
}