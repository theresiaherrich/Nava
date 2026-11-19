<?php
namespace App\Http\Controllers;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RekapPresensiController extends PortalBaseController
{
    public function index(Request $request)
    {
        $hariIni=now()->startOfDay();
        try {$bulan=Carbon::createFromFormat('Y-m',(string)$request->query('bulan',$hariIni->format('Y-m')))->startOfMonth();}
        catch(\Throwable $e){$bulan=$hariIni->copy()->startOfMonth();}
        $awal=$bulan->copy()->startOfWeek(CarbonInterface::MONDAY);$akhir=$bulan->copy()->endOfMonth()->endOfWeek(CarbonInterface::SUNDAY);
        $minggu=[];$log=[];$i=0;
        for($d=$awal->copy();$d->lte($akhir);$d->addDay(),$i++){
            $data=$this->logHarian($d,$hariIni);$dalam=$d->month===$bulan->month;
            $minggu[intdiv($i,7)][]=(object)['tanggal'=>$d->format('Y-m-d'),'hari'=>$d->day,'dalam_bulan'=>$dalam,'status'=>$data['status'],'libur'=>$data['status']==='libur'];
            if($dalam)$log[$d->format('Y-m-d')]=$data;
        }
        $terpilih=(string)$request->query('tanggal','');
        if(!isset($log[$terpilih]))$terpilih=array_key_first($log);
        return view('portal-orang-tua.rekap-presensi',[
            'siswa'=>$this->dataSiswa(),'ortu'=>$this->dataOrtu(),'ringkasan'=>$this->dataPresensiSemester(),'sinkronisasi'=>now(),
            'bulan'=>$bulan,'bulanPrev'=>$bulan->copy()->subMonth()->format('Y-m'),'bulanNext'=>$bulan->copy()->addMonth()->format('Y-m'),
            'bisaMaju'=>$bulan->lt($hariIni->copy()->startOfMonth()),'minggu'=>$minggu,'log'=>$log,'terpilih'=>$terpilih,'labelStatus'=>$this->labelStatus(),
        ]);
    }
    public function unduh(): RedirectResponse
    {
        return back()->with('toast',['type'=>'info','message'=>'Fitur unduh presensi belum tersedia.']);
    }
}
