<?php
namespace App\Http\Controllers;

class ProfilSiswaController extends PortalBaseController
{
    public function index()
    {
        return view('portal-orang-tua.profil-siswa',[
            'siswa'=>$this->dataSiswa(),'ortu'=>$this->dataOrtu(),'walimurid'=>$this->dataWaliMurid(),
            'kegiatan'=>$this->dataKegiatan(),'sinkronisasi'=>now(),
        ]);
    }
}
