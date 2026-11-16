<?php

namespace App\Http\Controllers\A3_BK\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\A3_BK\Concerns\RecordsA3Activity;
use App\Models\DimensiRiasec;
use App\Models\Mapel;
use Illuminate\Http\Request;

/**
 * MapelPilihanController — CRUD mata pelajaran pilihan + profil RIASEC-nya.
 * Semua atribut RIASEC disimpan langsung pada tabel `mapel`.
 */
class MapelPilihanController extends Controller
{
    use RecordsA3Activity;

    public function index()
    {
        $mapel = Mapel::where('is_pilihan', 1)->get();

        return view('bk.admin.mapel.index', compact('mapel'));
    }

    public function create()
    {
        return view('bk.admin.mapel.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode'         => 'required|string|max:30|unique:mapel,kode',
            'nama'         => 'required|string|max:120',
            'rumpun'       => 'required|string|max:50',
            'kode_riasec'  => ['required', 'string', 'size:3', 'regex:/^[RIASEC]{3}$/', function ($attr, $value, $fail) {
                if (count(array_unique(str_split($value))) !== 3) {
                    $fail('Profil RIASEC harus 3 huruf berbeda dari R,I,A,S,E,C.');
                }
            }],
            'deskripsi'    => 'nullable|string',
        ]);

        $kodeRiasec = strtoupper($request->kode_riasec);
        $idDimensi = DimensiRiasec::where('kode', substr($kodeRiasec, 0, 1))->value('id_dimensi');

        if (! $idDimensi) {
            abort(422, 'Dimensi RIASEC untuk profil ini tidak ditemukan.');
        }

        $mapel = Mapel::create([
            'id_dimensi'      => $idDimensi,
            'kode'            => strtoupper($request->kode),
            'nama'            => $request->nama,
            'rumpun'          => $request->rumpun,
            'kode_riasec'     => $kodeRiasec,
            'deskripsi'       => $request->deskripsi,
            'is_pilihan'      => 1,
            'aktif'           => 1,
            'status_validasi' => 'menunggu',
        ]);

        $this->recordA3Activity('ditambahkan', 'mapel', $mapel->id_mapel);

        return redirect()->route('admin.mapel')->with('success', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function edit(int $id)
    {
        $mapel = Mapel::findOrFail($id);

        return view('bk.admin.mapel.edit', compact('mapel'));
    }

    public function update(Request $request, int $id)
    {
        $mapel = Mapel::findOrFail($id);

        $request->validate([
            'kode'         => 'required|string|max:30|unique:mapel,kode,' . $id . ',id_mapel',
            'nama'         => 'required|string|max:120',
            'rumpun'       => 'required|string|max:50',
            'kode_riasec'  => ['required', 'string', 'size:3', 'regex:/^[RIASEC]{3}$/', function ($attr, $value, $fail) {
                if (count(array_unique(str_split($value))) !== 3) {
                    $fail('Profil RIASEC harus 3 huruf berbeda dari R,I,A,S,E,C.');
                }
            }],
            'deskripsi'    => 'nullable|string',
        ]);

        $kodeRiasec = strtoupper($request->kode_riasec);
        $idDimensi = DimensiRiasec::where('kode', substr($kodeRiasec, 0, 1))->value('id_dimensi');

        if (! $idDimensi) {
            abort(422, 'Dimensi RIASEC untuk profil ini tidak ditemukan.');
        }

        $mapel->update([
            'id_dimensi' => $idDimensi,
            'kode'        => strtoupper($request->kode),
            'nama'        => $request->nama,
            'rumpun'      => $request->rumpun,
            'kode_riasec' => $kodeRiasec,
            'deskripsi'   => $request->deskripsi,
            'status_validasi' => 'menunggu',
            'catatan_validasi' => null,
            'divalidasi_oleh' => null,
            'waktu_validasi' => null,
        ]);

        $this->recordA3Activity('diperbarui', 'mapel', $mapel->id_mapel);

        return redirect()->route('admin.mapel')->with('success', 'Mata pelajaran berhasil diperbarui.');
    }

    public function destroy(int $id)
    {
        $mapel = Mapel::findOrFail($id);
        $mapel->delete();

        $this->recordA3Activity('dihapus', 'mapel', $id);

        return redirect()->route('admin.mapel')->with('success', 'Mata pelajaran berhasil dihapus.');
    }
}
