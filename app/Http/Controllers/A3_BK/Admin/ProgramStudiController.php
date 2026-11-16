<?php

namespace App\Http\Controllers\A3_BK\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\A3_BK\Concerns\RecordsA3Activity;
use App\Models\DimensiRiasec;
use App\Models\Profesi;
use App\Models\ProgramStudi;
use Illuminate\Http\Request;

/**
 * ProgramStudiController — CRUD master data program studi.
 */
class ProgramStudiController extends Controller
{
    use RecordsA3Activity;

    public function index()
    {
        $prodi = ProgramStudi::with('profesi')->get();

        return view('bk.admin.prodi.index', compact('prodi'));
    }

    public function create()
    {
        $profesiList = Profesi::where('aktif', 1)->orderBy('nama')->get();

        return view('bk.admin.prodi.create', compact('profesiList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama'        => 'required|string|max:120|unique:program_studi,nama',
            'rumpun'      => 'nullable|string|max:60',
            'tentang'     => 'nullable|string',
            'deskripsi'   => 'nullable|string',
            'kode_riasec' => ['required', 'string', 'size:3', 'regex:/^[RIASEC]{3}$/', function ($attr, $value, $fail) {
                if (count(array_unique(str_split($value))) !== 3) {
                    $fail('Profil RIASEC harus 3 huruf berbeda dari R,I,A,S,E,C.');
                }
            }],
            'profesi'     => 'nullable|array',
            'profesi.*'   => 'exists:profesi,id_profesi',
        ]);

        $kodeRiasec = strtoupper($request->kode_riasec);
        $idDimensi = DimensiRiasec::where('kode', substr($kodeRiasec, 0, 1))->value('id_dimensi');

        if (! $idDimensi) {
            abort(422, 'Dimensi RIASEC untuk profil ini tidak ditemukan.');
        }

        $prodi = ProgramStudi::create([
            'id_dimensi'      => $idDimensi,
            'nama'            => $request->nama,
            'rumpun'          => $request->rumpun,
            'tentang'         => $request->tentang,
            'deskripsi'       => $request->deskripsi,
            'kode_riasec'     => $kodeRiasec,
            'aktif'           => 1,
            'status_validasi' => 'menunggu',
        ]);

        if ($request->filled('profesi')) {
            $prodi->profesi()->sync($request->profesi);
        }

        $this->recordA3Activity('ditambahkan', 'program_studi', $prodi->id_prodi);

        return redirect()->route('admin.prodi')->with('success', 'Program studi berhasil ditambahkan.');
    }

    public function edit(int $id)
    {
        $prodi = ProgramStudi::with('profesi')->findOrFail($id);
        $profesiList = Profesi::where('aktif', 1)->orderBy('nama')->get();

        return view('bk.admin.prodi.edit', compact('prodi', 'profesiList'));
    }

    public function update(Request $request, int $id)
    {
        $prodi = ProgramStudi::findOrFail($id);

        $request->validate([
            'nama'        => 'required|string|max:120|unique:program_studi,nama,' . $id . ',id_prodi',
            'rumpun'      => 'nullable|string|max:60',
            'tentang'     => 'nullable|string',
            'deskripsi'   => 'nullable|string',
            'kode_riasec' => ['required', 'string', 'size:3', 'regex:/^[RIASEC]{3}$/', function ($attr, $value, $fail) {
                if (count(array_unique(str_split($value))) !== 3) {
                    $fail('Profil RIASEC harus 3 huruf berbeda dari R,I,A,S,E,C.');
                }
            }],
            'profesi'     => 'nullable|array',
            'profesi.*'   => 'exists:profesi,id_profesi',
        ]);

        $kodeRiasec = strtoupper($request->kode_riasec);
        $idDimensi = DimensiRiasec::where('kode', substr($kodeRiasec, 0, 1))->value('id_dimensi');

        if (! $idDimensi) {
            abort(422, 'Dimensi RIASEC untuk profil ini tidak ditemukan.');
        }

        $prodi->update([
            'id_dimensi'      => $idDimensi,
            'nama'        => $request->nama,
            'rumpun'      => $request->rumpun,
            'tentang'     => $request->tentang,
            'deskripsi'   => $request->deskripsi,
            'kode_riasec' => $kodeRiasec,
            'status_validasi' => 'menunggu',
            'catatan_validasi' => null,
            'divalidasi_oleh' => null,
            'waktu_validasi' => null,
        ]);

        $prodi->profesi()->sync($request->profesi ?? []);

        $this->recordA3Activity('diperbarui', 'program_studi', $prodi->id_prodi);

        return redirect()->route('admin.prodi')->with('success', 'Program studi berhasil diperbarui.');
    }

    public function destroy(int $id)
    {
        $prodi = ProgramStudi::findOrFail($id);
        $prodi->delete();

        $this->recordA3Activity('dihapus', 'program_studi', $id);

        return redirect()->route('admin.prodi')->with('success', 'Program studi berhasil dihapus.');
    }
}
