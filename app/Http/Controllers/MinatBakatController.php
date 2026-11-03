<?php

namespace App\Http\Controllers;


/**
 * A4 - Portal Orang Tua: Minat-Bakat & Karir
 */
class MinatBakatController extends PortalBaseController
{
    public function index()
    {
        $riasec = $this->dataRiasec();

        return view('portal-orang-tua.minat-bakat', [
            'siswa'      => $this->dataSiswa(),
            'ortu'       => $this->dataOrtu(),
            'riasec'     => $riasec,
            'prodi'      => $this->dataRekomendasiProdi(),
            'tes'        => $this->dataTes(),
            'bk'         => $this->dataBk(),
            'kegiatan'   => $this->dataKegiatan(),
            'langkah'    => $this->dataLangkahKarier(),
        ]);
    }
}
