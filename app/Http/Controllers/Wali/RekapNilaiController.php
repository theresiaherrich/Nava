<?php

namespace App\Http\Controllers\Wali;

use App\Http\Controllers\Controller;
use App\Support\Dummy\Paginasi;
use App\Support\Dummy\Wali;
use Illuminate\Http\Request;

class RekapNilaiController extends Controller
{
    /** Jumlah kolom mapel yang tampil sebelum "Tampilkan semua mapel" (desain 4.01: 10 dari 16). */
    private const KOLOM_AWAL = 10;

    /**
     * Rekap Nilai (DKN) kelas wali (desain Wali 4.01–4.03).
     * Query: ?tab=siswa|mapel, ?periode=sts|semester, ?mapel=<id>|semua, ?q=, ?ekspor=1 (dropdown terbuka).
     */
    public function index(Request $request)
    {
        $dkn = Wali::dkn();
        $tab = $request->query('tab') === 'mapel' ? 'mapel' : 'siswa';
        $periode = $request->query('periode') === 'semester' ? 'semester' : 'sts';
        $mapel = collect($dkn['mapel']);
        $pilih = $request->query('mapel');

        $kolom = match (true) {
            $pilih === 'semua' => $mapel,
            $mapel->contains('id', $pilih) => $mapel->where('id', $pilih),
            default => $mapel->take(self::KOLOM_AWAL),
        };

        $siswa = collect($dkn['siswa']);
        $sel = $siswa->sum(fn ($s) => count(array_filter($s['nilai'], fn ($v) => $v !== null)));
        $harus = $mapel->sum('peserta');
        $rata = $siswa->whereNotNull('rata');

        return view('wali.rekap-nilai', [
            'kelas' => Wali::KELAS,
            'tab' => $tab,
            'periode' => $periode,
            'mapel' => $mapel->all(),
            'kolom' => $kolom->values()->all(),
            'semuaKolom' => $kolom->count() === $mapel->count(),
            'pilihMapel' => $pilih,
            'baris' => Paginasi::buat($siswa->filter(fn ($s) => Paginasi::cocok($request->query('q'), $s['nama'], $s['nisn']))->values(), $request),
            'statistik' => [
                'siswa' => $siswa->count(),
                'rata' => $rata->count() ? round($rata->avg('rata'), 1) : null,
                'kelengkapan' => $harus ? (int) round($sel / $harus * 100) : 0,
            ],
        ]);
    }
}
