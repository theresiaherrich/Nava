<?php

namespace App\Http\Controllers\A3_BK\Admin;

use App\Http\Controllers\Controller;
use App\Models\DimensiRiasec;
use App\Models\Mapel;
use App\Models\ProfilMapel;
use Illuminate\Http\Request;

/**
 * MapelPilihanController — CRUD mata pelajaran pilihan + profil RIASEC-nya.
 */
class MapelPilihanController extends Controller
{
    public function index()
    {
        $mapel = Mapel::with('profilMapel', 'dimensi')->get();

        return view('bk.admin.mapel.index', compact('mapel'));
    }

    public function create()
    {
        $dimensiList = DimensiRiasec::all();

        return view('bk.admin.mapel.create', compact('dimensiList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_dimensi' => 'required|exists:dimensi_riasec,id_dimensi',
            'kode' => 'required|string|max:30|unique:mapel,kode',
            'nama' => 'required|string|max:120',
            'rumpun' => 'required|string|max:50',
            'kode_holland' => ['required', 'string', 'size:3', 'regex:/^[RIASEC]{3}$/', function ($attr, $value, $fail) {
                if (count(array_unique(str_split($value))) !== 3) {
                    $fail('Profil RIASEC harus 3 huruf berbeda dari R,I,A,S,E,C.');
                }
            }],
            'deskripsi' => 'nullable|string',
        ]);

        $mapel = Mapel::create([
            'id_dimensi' => $request->id_dimensi,
            'kode' => strtoupper($request->kode),
            'nama' => $request->nama,
        ]);

        ProfilMapel::create([
            'id_mapel' => $mapel->id_mapel,
            'rumpun' => $request->rumpun,
            'deskripsi' => $request->deskripsi,
            'kode_holland' => strtoupper($request->kode_holland),
        ]);

        return redirect()->route('admin.mapel')->with('success', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function edit(int $id)
    {
        $mapel = Mapel::with('profilMapel', 'dimensi')->findOrFail($id);
        $dimensiList = DimensiRiasec::all();

        return view('bk.admin.mapel.edit', compact('mapel', 'dimensiList'));
    }

    public function update(Request $request, int $id)
    {
        $mapel = Mapel::findOrFail($id);

        $request->validate([
            'id_dimensi' => 'required|exists:dimensi_riasec,id_dimensi',
            'kode' => 'required|string|max:30|unique:mapel,kode,'.$id.',id_mapel',
            'nama' => 'required|string|max:120',
            'rumpun' => 'required|string|max:50',
            'kode_holland' => ['required', 'string', 'size:3', 'regex:/^[RIASEC]{3}$/', function ($attr, $value, $fail) {
                if (count(array_unique(str_split($value))) !== 3) {
                    $fail('Profil RIASEC harus 3 huruf berbeda dari R,I,A,S,E,C.');
                }
            }],
            'deskripsi' => 'nullable|string',
        ]);

        $mapel->update([
            'id_dimensi' => $request->id_dimensi,
            'kode' => strtoupper($request->kode),
            'nama' => $request->nama,
        ]);

        $mapel->profilMapel()->updateOrCreate(
            ['id_mapel' => $id],
            [
                'rumpun' => $request->rumpun,
                'deskripsi' => $request->deskripsi,
                'kode_holland' => strtoupper($request->kode_holland),
            ]
        );

        return redirect()->route('admin.mapel')->with('success', 'Mata pelajaran berhasil diperbarui.');
    }

    public function destroy(int $id)
    {
        Mapel::findOrFail($id)->delete();

        return redirect()->route('admin.mapel')->with('success', 'Mata pelajaran berhasil dihapus.');
    }
}
