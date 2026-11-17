<?php

namespace App\Http\Controllers\Wali;

use App\Http\Controllers\Controller;
use App\Support\Dummy\Wali;
use Illuminate\Http\Request;

class PelengkapRaporController extends Controller
{
    /**
     * Pelengkap Rapor wali kelas (desain Wali 3.01–3.03).
     * Varian: ?state=belum-a1|terkunci.
     */
    public function index(Request $request)
    {
        $state = in_array($request->query('state'), ['belum-a1', 'terkunci'], true) ? $request->query('state') : 'normal';

        return view('wali.pelengkap-rapor', [
            'kelas' => Wali::KELAS,
            'state' => $state,
            'siswa' => Wali::pelengkap($state),
            'diperbarui' => '12 Okt 2026, 07.00',
            'templat' => ['Aktif & bertanggung jawab', 'Perlu meningkatkan kehadiran', 'Pertahankan prestasi'],
        ]);
    }
}
