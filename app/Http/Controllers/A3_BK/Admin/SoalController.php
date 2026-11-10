<?php

namespace App\Http\Controllers\A3_BK\Admin;

use App\Http\Controllers\Controller;
use App\Models\DimensiRiasec;
use App\Models\SoalRiasec;
use Illuminate\Http\Request;

/**
 * SoalController — CRUD soal tes RIASEC.
 */
class SoalController extends Controller
{
    public function index()
    {
        $soal = SoalRiasec::with('dimensi')->orderBy('id_soal_riasec')->get();
        $dimensiList = DimensiRiasec::all();

        return view('bk.admin.soal.index', compact('soal', 'dimensiList'));
    }

    public function create()
    {
        $dimensiList = DimensiRiasec::all();

        return view('bk.admin.soal.create', compact('dimensiList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_dimensi' => 'required|exists:dimensi_riasec,id_dimensi',
            'teks' => 'required|string|min:10',
            'aktif' => 'boolean',
        ]);

        SoalRiasec::create([
            'id_dimensi' => $request->id_dimensi,
            'teks' => $request->teks,
            'aktif' => $request->boolean('aktif', true),
        ]);

        return redirect()->route('admin.soal')->with('success', 'Soal berhasil ditambahkan.');
    }

    public function edit(int $id)
    {
        $soal = SoalRiasec::with('dimensi')->findOrFail($id);
        $dimensiList = DimensiRiasec::all();

        return view('bk.admin.soal.edit', compact('soal', 'dimensiList'));
    }

    public function update(Request $request, int $id)
    {
        $request->validate([
            'id_dimensi' => 'required|exists:dimensi_riasec,id_dimensi',
            'teks' => 'required|string|min:10',
            'aktif' => 'boolean',
        ]);

        SoalRiasec::findOrFail($id)->update([
            'id_dimensi' => $request->id_dimensi,
            'teks' => $request->teks,
            'aktif' => $request->boolean('aktif', false),
        ]);

        return redirect()->route('admin.soal')->with('success', 'Soal berhasil diperbarui.');
    }

    public function destroy(int $id)
    {
        SoalRiasec::findOrFail($id)->delete();

        return redirect()->route('admin.soal')->with('success', 'Soal berhasil dihapus.');
    }

    public function toggle(int $id)
    {
        $soal = SoalRiasec::findOrFail($id);
        $soal->update(['aktif' => ! $soal->aktif]);
        $status = $soal->aktif ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('admin.soal')->with('success', "Soal berhasil {$status}.");
    }
}
