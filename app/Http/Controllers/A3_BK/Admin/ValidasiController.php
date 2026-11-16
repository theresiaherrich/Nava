<?php

namespace App\Http\Controllers\A3_BK\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mapel;
use App\Models\ProgramStudi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * ValidasiController — validasi profil mapel dan program studi oleh Guru BK.
 */
class ValidasiController extends Controller
{
    public function index()
    {
        $mapelMenunggu = Mapel::where('status_validasi', 'menunggu')
            ->where('is_pilihan', true)
            ->orderBy('nama')
            ->get();
        $prodiMenunggu = ProgramStudi::where('status_validasi', 'menunggu')
            ->orderBy('nama')
            ->get();

        return view('bk.admin.validasi.index', compact('mapelMenunggu', 'prodiMenunggu'));
    }

    public function validasiMapel(Request $request, int $id)
    {
        $request->validate([
            'status_validasi'  => 'required|in:tervalidasi,perlu_revisi',
            'catatan_validasi' => 'nullable|required_if:status_validasi,perlu_revisi|string',
        ]);

        $mapel = Mapel::findOrFail($id);
        $mapel->update([
            'status_validasi'  => $request->status_validasi,
            'catatan_validasi' => $request->input('catatan_validasi'),
            'divalidasi_oleh'  => Auth::id(),
            'waktu_validasi'   => now(),
        ]);

        $this->logValidation('mapel', $mapel->id_mapel, $request->status_validasi);

        return redirect()->route('bk.validasi')->with('success', 'Status validasi mapel diperbarui.');
    }

    public function validasiProdi(Request $request, int $id)
    {
        $request->validate([
            'status_validasi'  => 'required|in:tervalidasi,perlu_revisi',
            'catatan_validasi' => 'nullable|required_if:status_validasi,perlu_revisi|string',
        ]);

        $prodi = ProgramStudi::findOrFail($id);
        $prodi->update([
            'status_validasi'  => $request->status_validasi,
            'catatan_validasi' => $request->input('catatan_validasi'),
            'divalidasi_oleh'  => Auth::id(),
            'waktu_validasi'   => now(),
        ]);

        $this->logValidation('program_studi', $prodi->id_prodi, $request->status_validasi);

        return redirect()->route('bk.validasi')->with('success', 'Status validasi program studi diperbarui.');
    }

    private function logValidation(string $entity, int $entityId, string $status): void
    {
        DB::table('log_aktivitas')->insert([
            'id_user' => Auth::id(),
            'aksi' => 'validasi_'.$status,
            'nama_entitas' => $entity,
            'entitas_id' => $entityId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
