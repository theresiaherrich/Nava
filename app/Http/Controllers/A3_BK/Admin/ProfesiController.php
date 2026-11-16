<?php

namespace App\Http\Controllers\A3_BK\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\A3_BK\Concerns\RecordsA3Activity;
use App\Models\Profesi;
use App\Models\ProgramStudi;
use Illuminate\Http\Request;

/**
 * ProfesiController — CRUD profesi.
 */
class ProfesiController extends Controller
{
    use RecordsA3Activity;

    public function index()
    {
        $profesi = Profesi::with('programStudi')->orderBy('nama')->get();

        return view('bk.admin.profesi.index', compact('profesi'));
    }

    public function create()
    {
        $prodiList = ProgramStudi::orderBy('nama')->get();

        return view('bk.admin.profesi.create', compact('prodiList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:120|unique:profesi,nama',
            'deskripsi' => 'required|string',
            'prodi' => 'nullable|array',
            'prodi.*' => 'exists:program_studi,id_prodi',
        ]);

        $profesi = Profesi::create([
            'nama' => $request->nama,
            'deskripsi' => $request->deskripsi,
        ]);

        if ($request->filled('prodi')) {
            $profesi->programStudi()->sync($request->prodi);
        }

        $this->recordA3Activity('ditambahkan', 'profesi', $profesi->id_profesi);

        return redirect()->route('admin.profesi')->with('success', 'Profesi berhasil ditambahkan.');
    }

    public function edit(int $id)
    {
        $profesi = Profesi::with('programStudi')->findOrFail($id);
        $prodiList = ProgramStudi::orderBy('nama')->get();

        return view('bk.admin.profesi.edit', compact('profesi', 'prodiList'));
    }

    public function update(Request $request, int $id)
    {
        $profesi = Profesi::findOrFail($id);

        $request->validate([
            'nama' => 'required|string|max:120|unique:profesi,nama,'.$id.',id_profesi',
            'deskripsi' => 'required|string',
            'prodi' => 'nullable|array',
            'prodi.*' => 'exists:program_studi,id_prodi',
        ]);

        $profesi->update(['nama' => $request->nama, 'deskripsi' => $request->deskripsi]);
        $profesi->programStudi()->sync($request->prodi ?? []);

        $this->recordA3Activity('diperbarui', 'profesi', $profesi->id_profesi);

        return redirect()->route('admin.profesi')->with('success', 'Profesi berhasil diperbarui.');
    }

    public function destroy(int $id)
    {
        Profesi::findOrFail($id)->delete();
        $this->recordA3Activity('dihapus', 'profesi', $id);

        return redirect()->route('admin.profesi')->with('success', 'Profesi berhasil dihapus.');
    }
}
