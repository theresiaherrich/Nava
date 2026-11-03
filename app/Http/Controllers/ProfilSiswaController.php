<?php

namespace App\Http\Controllers;


/**
 * A4 - Portal Orang Tua: Profil Siswa
 */
class ProfilSiswaController extends PortalBaseController
{
    public function index()
    {
        return view('portal-orang-tua.profil-siswa', [
            'siswa'     => $this->dataSiswa(),
            'ortu'      => $this->dataOrtu(),
            'walimurid' => $this->dataWaliMurid(),
            'kegiatan'  => $this->dataKegiatan(),
            'sinkronisasi' => now()->subDays(9)->setTime(7, 30),
        ]);
    }
}
