<?php

namespace App\Http\Controllers;

use App\Support\Dummy\Operator;
use App\Support\Dummy\Paginasi;
use Illuminate\Http\Request;

class RelasiOrangTuaController extends Controller
{
    /** Relasi Orang Tua – Anak (desain 2.09). Varian: ?modal=hubungkan (2.10). */
    public function index(Request $request)
    {
        $semua = Operator::relasi();

        $tersaring = Paginasi::saring($semua, [
            $request->query('status') => fn ($r, $v) => $r['status'] === $v,
            $request->query('hubungan') => fn ($r, $v) => $r['hubungan'] === $v,
        ])->filter(fn ($r) => Paginasi::cocok(
            $request->query('q'), $r['nama'], $r['username'], ...array_column($r['anak'], 'nama'),
        ))->values();

        return view('relasi-orang-tua.index', [
            'relasi' => Paginasi::buat($tersaring, $request, 8),
            'jumlah' => $semua->count(),
            'belumTerhubung' => Operator::siswa()->where('orang_tua', 'belum')->count(),
            // Pilihan modal Hubungkan. TODO API: GET /api/akun?peran=ORANG_TUA&q=, GET /api/master/siswa?q=
            'akunOrtu' => $semua->take(40)->map(fn ($r) => ['id' => $r['id_orang_tua'], 'nama' => $r['nama'], 'username' => $r['username']])->values()->all(),
            'calonAnak' => Operator::calonAnak(),
        ]);
    }
}
