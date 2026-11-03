<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * A4 - Portal Orang Tua: Rekap Presensi
 */
class RekapPresensiController extends PortalBaseController
{
    public function index(Request $request)
    {
        $hariIni = now()->startOfDay();

        // ?bulan=2026-11  (default: bulan berjalan)
        try {
            $bulan = Carbon::createFromFormat('Y-m', (string) $request->query('bulan', $hariIni->format('Y-m')))
                ->startOfMonth();
        } catch (\Throwable $e) {
            $bulan = $hariIni->copy()->startOfMonth();
        }

        // Susun sel kalender Senin..Minggu
        $awal  = $bulan->copy()->startOfWeek(CarbonInterface::MONDAY);
        $akhir = $bulan->copy()->endOfMonth()->endOfWeek(CarbonInterface::SUNDAY);

        $minggu = [];
        $log    = [];
        $i      = 0;
        for ($d = $awal->copy(); $d->lte($akhir); $d->addDay(), $i++) {
            $dalamBulan = $d->month === $bulan->month;
            $data       = $this->logHarian($d, $hariIni);

            $minggu[intdiv($i, 7)][] = (object) [
                'tanggal'     => $d->format('Y-m-d'),
                'hari'        => $d->day,
                'dalam_bulan' => $dalamBulan,
                'status'      => $data['status'],
                'libur'       => $data['status'] === 'libur',
            ];

            if ($dalamBulan) {
                $log[$d->format('Y-m-d')] = $data;
            }
        }

        // Tanggal terpilih: ?tanggal=..., kalau tidak ada -> hari ini / hari sekolah terakhir di bulan itu
        $terpilih = (string) $request->query('tanggal', '');
        if (! isset($log[$terpilih])) {
            $terpilih = collect($log)->filter(fn ($l) => ! in_array($l['status'], ['libur', 'belum']))
                ->keys()->last() ?? array_key_first($log);
        }

        return view('portal-orang-tua.rekap-presensi', [
            'siswa'      => $this->dataSiswa(),
            'ortu'       => $this->dataOrtu(),
            'ringkasan'  => $this->dataPresensiSemester(),
            'sinkronisasi' => now()->setTime(15, 42),
            'bulan'      => $bulan,
            'bulanPrev'  => $bulan->copy()->subMonth()->format('Y-m'),
            'bulanNext'  => $bulan->copy()->addMonth()->format('Y-m'),
            'bisaMaju'   => $bulan->lt($hariIni->copy()->startOfMonth()),
            'minggu'     => $minggu,
            'log'        => $log,
            'terpilih'   => $terpilih,
            'labelStatus' => $this->labelStatus(),
        ]);
    }


    /** Placeholder, ganti dengan generator PDF. */
    public function unduh(): RedirectResponse
    {
        return back()->with('toast', ['type' => 'info', 'message' => 'Unduh rekap presensi PDF belum tersambung ke generator PDF.']);
    }
}
