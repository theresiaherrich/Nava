<?php
namespace App\Http\Controllers;

class PortalDashboardController extends PortalBaseController
{
    public function index()
    {
        $nilai=$this->dataNilai();
        $rata=$this->rataRata($nilai);
        $prodi=$this->dataRekomendasiProdi();
        return view('portal-orang-tua.dashboard',[
            'siswa'=>$this->dataSiswa(),'ortu'=>$this->dataOrtu(),'sapaan'=>$this->sapaan(),'sinkronisasi'=>now(),
            'presensi'=>$this->dataPresensiSemester(),'hariIni'=>$this->dataKehadiranHariIni(),
            'rataRata'=>$rata,'predikat'=>$nilai->isEmpty()?'—':$this->predikat($rata),'peringkat'=>null,
            'mapelUnggul'=>$nilai->filter(fn($n)=>$n->nilai !== null)->sortByDesc('nilai')->take(3)->values(),'riasec'=>$this->dataRiasec(),
            'prodiTop'=>$prodi[0]??null,'tes'=>$this->dataTes(),'bk'=>$this->dataBk(),'pengumuman'=>[], 'kktp'=>self::KKTP,
        ]);
    }
}
