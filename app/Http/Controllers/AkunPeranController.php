<?php

namespace App\Http\Controllers;

use App\Support\Dummy\Operator;
use App\Support\Dummy\Paginasi;
use Illuminate\Http\Request;

class AkunPeranController extends Controller
{
    /** Kelola Akun & Peran (desain 2.02). Varian: ?modal=akun (2.03, contoh error username). */
    public function index(Request $request)
    {
        $semua = Operator::akun();

        $tersaring = Paginasi::saring($semua, [
            $request->query('peran') => fn ($r, $v) => in_array($v, $r['peran'], true),
            $request->query('status') => fn ($r, $v) => $r['status'] === $v,
        ])->filter(fn ($r) => Paginasi::cocok($request->query('q'), $r['nama'], $r['username']))->values();

        $guru = Operator::guru();

        return view('akun-peran.index', [
            'akun' => Paginasi::buat($tersaring, $request),
            'statistik' => [
                'total' => $semua->count(),
                'aktif' => $semua->where('status', 'aktif')->count(),
                'nonaktif' => $semua->where('status', 'nonaktif')->count(),
                'guru' => $guru->count(),
                'guru_tanpa_akun' => $guru->where('akun', 'belum')->count(),
                'ortu' => Operator::relasi()->count(),
            ],
            'peran' => Operator::PERAN,
            // Dipakai validasi "Username sudah dipakai" di modal. TODO API: GET /api/akun/cek-username?u=
            'username' => $semua->pluck('username')->all(),
        ]);
    }
}
