<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * A4 - Portal Orang Tua: Akademik & Nilai
 */
class AkademikNilaiController extends PortalBaseController
{
    public function index(Request $request)
    {
        $nilai    = $this->dataNilai();
        $rataRata = $this->rataRata($nilai);

        $optimal = $nilai->where('nilai', '>=', self::NILAI_OPTIMAL)->count();

        return view('portal-orang-tua.akademik-nilai', [
            'siswa'        => $this->dataSiswa(),
            'ortu'         => $this->dataOrtu(),
            'nilai'        => $nilai,
            'rataRata'     => $rataRata,
            'selisih'      => 1.8,                       // vs semester sebelumnya
            'semesterLabel' => 'Semester Ganjil 2026/2027',
            'periodeLabel' => 'STS (Sumatif Tengah Semester)',
            'diverifikasi' => true,
            'disahkanPada' => Carbon::create(2026, 10, 21),
            'catatan'      => $this->dataCatatanSekolah(),
            'stabilitas'   => (object) [
                'total'   => $nilai->count(),
                'optimal' => $optimal,
                'persen'  => (int) round($rataRata),
                'predikat' => $this->predikat($rataRata),
                'ambang'  => self::NILAI_OPTIMAL,
            ],
            'tertinggi'    => $nilai->sortByDesc('nilai')->first(),
            'terendah'     => $nilai->sortBy('nilai')->first(),
            'portofolio'   => $this->dataPortofolio(),
            'target'       => ['teks' => 'Lolos Seleksi Kota OSN Matematika', 'status' => 'On-Track'],
            'kktp'         => self::KKTP,
            'q'            => (string) $request->query('q', ''),
        ]);
    }


    /** Placeholder, ganti dengan generator PDF. */
    public function unduh(): RedirectResponse
    {
        return back()->with('toast', ['type' => 'info', 'message' => 'Unduh rapor PDF belum tersambung ke generator PDF.']);
    }
}
