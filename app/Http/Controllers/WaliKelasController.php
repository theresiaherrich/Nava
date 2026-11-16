<?php

namespace App\Http\Controllers;

use App\Support\Dummy\WaliKelas;
use Illuminate\Http\Request;

class WaliKelasController extends Controller
{
    /** Penetapan Wali Kelas. Varian desain: ?state=diubah (2 perubahan belum disimpan, Kurikulum 4.10). */
    public function index(Request $request)
    {
        return view('kurikulum.wali-kelas', [
            'kelas' => WaliKelas::kelas(),
            'guru' => WaliKelas::guru(),
            'perubahan' => $request->query('state') === 'diubah' ? WaliKelas::contohPerubahan() : [],
        ]);
    }
}
