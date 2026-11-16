<?php

namespace App\Http\Controllers;

use App\Support\Dummy\Rombel;

class RombelController extends Controller
{
    /** Rombel & Pembagian Mengajar. Varian desain: ?pilih-guru=jerman-1 (4.06), ?modal=buat-rombel (4.07). */
    public function index()
    {
        return view('kurikulum.rombel', [
            'rombel' => Rombel::semua(),
            'calonPengampu' => Rombel::calonPengampu(),
            'daftarMapel' => Rombel::daftarMapel(),
            'daftarKelas' => Rombel::daftarKelas(),
        ]);
    }

    /** Detail Rombel. Varian desain: ?modal=tambah-peserta (4.09). */
    public function show(string $rombel)
    {
        $detail = Rombel::detail($rombel);
        abort_if($detail === null, 404);

        return view('kurikulum.rombel-detail', [
            'rombel' => $detail,
            'calonPeserta' => Rombel::calonPeserta($detail),
            'calonPengampu' => Rombel::calonPengampu(),
        ]);
    }
}
