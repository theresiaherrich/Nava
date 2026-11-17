<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Support\Dummy\Guru;
use Illuminate\Http\Request;

class TpController extends Controller
{
    /**
     * Kelola Tujuan Pembelajaran (desain Guru 06–18).
     * Varian: ?rombel=mtk-x-c (kosong), ?rombel=mtk-xi-a (kuota penuh), ?state=terkunci,
     *         ?tampil=sts, ?modal=tambah|tambah-error|salin|tidak-dihapus, ?pilih-rombel=1.
     */
    public function index(Request $request)
    {
        return view('guru.tp', [
            'rombel' => Guru::rombel(),
            'aktif' => Guru::cariRombel($request->query('rombel'))['id'],
            'stsTerkunci' => $request->query('state') === 'terkunci',
            'maks' => Guru::MAKS_TP,
        ]);
    }
}
