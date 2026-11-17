<?php

namespace App\Http\Controllers\Kurikulum;

use App\Http\Controllers\Controller;
use App\Support\Dummy\Kurikulum;
use App\Support\Dummy\Operator;
use Illuminate\Http\Request;

class RekapNilaiController extends Controller
{
    /** Rekap Nilai (DKN) seluruh kelas (desain 6.01–6.02). Tab: ?tab=siswa|kelas. */
    public function index(Request $request)
    {
        $tingkat = in_array($request->query('tingkat'), Operator::TINGKAT, true) ? $request->query('tingkat') : 'X';
        $periode = $request->query('periode') === 'semester' ? 'semester' : 'sts';
        $kelasTingkat = Kurikulum::statusKelas()->where('tingkat', $tingkat)->values();
        $kelasDipilih = $kelasTingkat->firstWhere('kelas', $request->query('kelas'))['kelas'] ?? null;
        $tab = $request->query('tab', $kelasDipilih ? 'siswa' : 'kelas');

        $terkunci = $kelasTingkat->where('kunci', 'terkunci');

        return view('kurikulum.rekap-nilai', [
            'tingkat' => $tingkat,
            'periode' => $periode,
            'tab' => $tab,
            'kelasDipilih' => $kelasDipilih,
            'kelas' => $kelasTingkat,
            'mapel' => array_slice(Kurikulum::MAPEL, 0, 9),
            'jumlahMapel' => count(Kurikulum::MAPEL),
            'nilaiSiswa' => $kelasDipilih ? Kurikulum::nilaiSiswa($kelasDipilih, $periode) : [],
            'statistik' => [
                'siswa' => $kelasTingkat->sum('jumlah_siswa'),
                'rata' => $periode === 'semester' ? '83,4' : '82,7',
                'kelengkapan' => (int) round($kelasTingkat->avg('kelengkapan')),
                'terkunci' => $terkunci->count(),
                'kelas_terkunci' => $terkunci->pluck('kelas')->implode(', '),
            ],
        ]);
    }
}
