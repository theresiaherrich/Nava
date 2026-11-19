<?php
namespace App\Http\Controllers;

class MinatBakatController extends PortalBaseController
{
    public function index()
    {
        return view('portal-orang-tua.minat-bakat',[
            'siswa'=>$this->dataSiswa(),'ortu'=>$this->dataOrtu(),'riasec'=>$this->dataRiasec(),
            'prodi'=>$this->dataRekomendasiProdi(),'tes'=>$this->dataTes(),'bk'=>$this->dataBk(),
            'kegiatan'=>$this->dataKegiatan(),'langkah'=>[],
        ]);
    }
}
