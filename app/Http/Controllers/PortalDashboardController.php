<?php

namespace App\Http\Controllers;


/**
 * A4 - Portal Orang Tua: Dashboard
 */
class PortalDashboardController extends PortalBaseController
{
    public function index()
    {
        $nilai    = $this->dataNilai();
        $presensi = $this->dataPresensiSemester();

        return view('portal-orang-tua.dashboard', [
            'siswa'        => $this->dataSiswa(),
            'ortu'         => $this->dataOrtu(),
            'sapaan'       => $this->sapaan(),
            'sinkronisasi' => now()->subMinutes(20),
            'presensi'     => $presensi,
            'hariIni'      => $this->dataKehadiranHariIni(),
            'rataRata'     => $this->rataRata($nilai),
            'predikat'     => $this->predikat($this->rataRata($nilai)),
            'peringkat'    => ['posisi' => 4, 'dari' => 36],
            'mapelUnggul'  => $nilai->sortByDesc('nilai')->take(3)->values(),
            'riasec'       => $this->dataRiasec(),
            'prodiTop'     => $this->dataRekomendasiProdi()[0],
            'tes'          => $this->dataTes(),
            'bk'           => $this->dataBk(),
            'pengumuman'   => $this->dataPengumuman(),
            'kktp'         => self::KKTP,
        ]);
    }
}
