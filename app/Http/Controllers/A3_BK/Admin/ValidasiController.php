<?php

namespace App\Http\Controllers\A3_BK\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProfilMapel;
use App\Models\ProgramStudi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * ValidasiController — validasi profil mapel dan program studi oleh admin BK.
 */
class ValidasiController extends Controller
{
    public function index()
    {
        $mapelMenunggu = ProfilMapel::with('mapel')
            ->where('status_validasi', 'menunggu')
            ->get();

        $prodiMenunggu = ProgramStudi::where('status_validasi', 'menunggu')->get();

        return view('bk.admin.validasi.index', compact('mapelMenunggu', 'prodiMenunggu'));
    }

    public function validasiMapel(Request $request, int $id)
    {
        $request->validate([
            'status_validasi' => 'required|in:tervalidasi,perlu_revisi',
            'catatan_validasi' => 'nullable|string',
        ]);

        $profil = ProfilMapel::findOrFail($id);
        $profil->update([
            'status_validasi' => $request->status_validasi,
            'catatan_validasi' => $request->catatan_validasi,
            'divalidasi_oleh' => Auth::id(),
            'waktu_validasi' => now(),
        ]);

        return redirect()->route('admin.validasi')->with('success', 'Status validasi mapel diperbarui.');
    }

    public function validasiProdi(Request $request, int $id)
    {
        $request->validate([
            'status_validasi' => 'required|in:tervalidasi,perlu_revisi',
            'catatan_validasi' => 'nullable|string',
        ]);

        $prodi = ProgramStudi::findOrFail($id);
        $prodi->update([
            'status_validasi' => $request->status_validasi,
            'catatan_validasi' => $request->catatan_validasi,
            'divalidasi_oleh' => Auth::id(),
            'waktu_validasi' => now(),
        ]);

        return redirect()->route('admin.validasi')->with('success', 'Status validasi program studi diperbarui.');
    }
}
