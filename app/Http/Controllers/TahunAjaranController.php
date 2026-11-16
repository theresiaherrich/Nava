<?php

namespace App\Http\Controllers;

use App\Support\Dummy\TahunAjaran;

class TahunAjaranController extends Controller
{
    /** Tahun Ajaran & Semester. Varian desain: ?modal=aktifkan (Kurikulum 4.02). */
    public function index()
    {
        return view('kurikulum.tahun-ajaran', [
            'semester' => TahunAjaran::semester(),
            'ringkasan' => TahunAjaran::ringkasanAktivasi(),
        ]);
    }
}
