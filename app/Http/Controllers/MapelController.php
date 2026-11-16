<?php

namespace App\Http\Controllers;

use App\Support\Dummy\MataPelajaran;

class MapelController extends Controller
{
    /** Mata Pelajaran. Varian desain: ?modal=mapel (Kurikulum 4.04). */
    public function index()
    {
        return view('kurikulum.mapel', [
            'mapel' => MataPelajaran::semua(),
            'kelompok' => MataPelajaran::KELOMPOK,
        ]);
    }
}
