<?php

namespace App\Http\Controllers\A3_BK\Admin;

use App\Http\Controllers\Controller;
use App\Models\Profesi;
use App\Models\ProgramStudi;
use Illuminate\Http\Request;

/**
 * ProgramStudiController — CRUD program studi.
 */
class ProgramStudiController extends Controller
{
    public function index()
    {
        $prodi = ProgramStudi::withCount('profesi')->orderBy('nama')->get();

        return view('bk.admin.prodi.index', compact('prodi'));
    }

    public function create()
    {
        $profesiList = Profesi::orderBy('nama')->get();

        return view('bk.admin.prodi.create', compact('profesiList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:120|unique:program_studi,nama',
            'kategori' => 'nullable|string|max:60',
            'deskripsi' => 'required|string',
            'kode_holland' => ['required', 'string', 'size:3', 'regex:/^[RIASEC]{3}$/', function ($attr, $value, $fail) {
                if (count(array_unique(str_split($value))) !== 3) {
                    $fail('Profil RIASEC harus 3 huruf berbeda dari R,I,A,S,E,C.');
                }
            }],
            'profesi' => 'nullable|array',
            'profesi.*' => 'exists:profesi,id_profesi',
        ]);

        $prodi = ProgramStudi::create([
            'nama' => $request->nama,
            'kategori' => $request->kategori,
            'deskripsi' => $request->deskripsi,
            'kode_holland' => strtoupper($request->kode_holland),
        ]);

        if ($request->filled('profesi')) {
            $prodi->profesi()->sync($request->profesi);
        }

        return redirect()->route('admin.prodi')->with('success', 'Program studi berhasil ditambahkan.');
    }

    public function edit(int $id)
    {
        $prodi = ProgramStudi::with('profesi')->findOrFail($id);
        $profesiList = Profesi::orderBy('nama')->get();

        return view('bk.admin.prodi.edit', compact('prodi', 'profesiList'));
    }

    public function update(Request $request, int $id)
    {
        $prodi = ProgramStudi::findOrFail($id);

        $request->validate([
            'nama' => 'required|string|max:120|unique:program_studi,nama,'.$id.',id_prodi',
            'kategori' => 'nullable|string|max:60',
            'deskripsi' => 'required|string',
            'kode_holland' => ['required', 'string', 'size:3', 'regex:/^[RIASEC]{3}$/', function ($attr, $value, $fail) {
                if (count(array_unique(str_split($value))) !== 3) {
                    $fail('Profil RIASEC harus 3 huruf berbeda dari R,I,A,S,E,C.');
                }
            }],
            'profesi' => 'nullable|array',
            'profesi.*' => 'exists:profesi,id_profesi',
        ]);

        $prodi->update([
            'nama' => $request->nama,
            'kategori' => $request->kategori,
            'deskripsi' => $request->deskripsi,
            'kode_holland' => strtoupper($request->kode_holland),
        ]);

        $prodi->profesi()->sync($request->profesi ?? []);

        return redirect()->route('admin.prodi')->with('success', 'Program studi berhasil diperbarui.');
    }

    public function destroy(int $id)
    {
        ProgramStudi::findOrFail($id)->delete();

        return redirect()->route('admin.prodi')->with('success', 'Program studi berhasil dihapus.');
    }
}
