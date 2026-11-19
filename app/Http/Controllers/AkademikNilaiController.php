<?php
namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AkademikNilaiController extends PortalBaseController
{
    public function index(Request $request)
    {
        $nilai=$this->dataNilai();$rata=$this->rataRata($nilai);$rk=$this->riwayatAktif();
        $q=trim((string)$request->query('q',''));
        if($q!=='')$nilai=$nilai->filter(fn($n)=>mb_stripos($n->mapel,$q)!==false)->values();
        $rapor=$rk?DB::table('rapor as rp')->join('periode_nilai as pn','pn.id_periode','=','rp.id_periode')->where('rp.id_riwayat_kelas',$rk->id_riwayat_kelas)->orderByDesc('rp.waktu_terbit')->first():null;
        return view('portal-orang-tua.akademik-nilai',[
            'siswa'=>$this->dataSiswa(),'ortu'=>$this->dataOrtu(),'nilai'=>$nilai,'rataRata'=>$rata,'selisih'=>null,
            'semesterLabel'=>$rk?ucfirst(strtolower($rk->semester_nama)).' '.$rk->tahun_ajaran:'Semester belum tersedia',
            'periodeLabel'=>$rapor?($rapor->jenis.' · '.$rapor->status):'Periode rapor belum tersedia',
            'diverifikasi'=>$rapor && in_array($rapor->status,['siap','terbit']),
            'disahkanPada'=>$rapor && $rapor->waktu_terbit ? \Carbon\Carbon::parse($rapor->waktu_terbit):null,
            'catatan'=>$this->dataCatatanSekolah(),
            'stabilitas'=>(object)['total'=>$nilai->filter(fn($n)=>$n->nilai !== null)->count(),'optimal'=>$nilai->where('nilai','>=',self::NILAI_OPTIMAL)->count(),'persen'=>(int)round($rata),'predikat'=>$nilai->isEmpty()?'—':$this->predikat($rata),'ambang'=>self::NILAI_OPTIMAL],
            'tertinggi'=>$nilai->sortByDesc('nilai')->first(),'terendah'=>$nilai->sortBy('nilai')->first(),
            'portofolio'=>[],'target'=>null,'kktp'=>self::KKTP,'q'=>$q,
        ]);
    }
    public function unduh(): RedirectResponse
    {
        return back()->with('toast',['type'=>'info','message'=>'Unduhan rapor belum tersedia.']);
    }
}
