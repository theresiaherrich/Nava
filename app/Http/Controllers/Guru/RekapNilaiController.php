<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Support\Dummy\DashboardGuru;
use App\Support\Dummy\Guru;
use App\Support\Dummy\Paginasi;
use Illuminate\Http\Request;

class RekapNilaiController extends Controller
{
    private const KKTP = 75;

    /**
     * Rekap Nilai (DKN) guru (desain Guru 30–33). Tab: ?tab=ringkasan|siswa, ?rombel=, ?periode=sts|semester.
     * TODO API: GET /api/guru/rekap?rombel=&periode=
     */
    public function index(Request $request)
    {
        $rombel = Guru::rombel();
        $status = collect(DashboardGuru::data()['kelas'])->keyBy('id_rombel');
        $periode = $request->query('periode') === 'sts' ? 'sts' : 'semester';
        $tab = $request->query('tab', $request->has('rombel') ? 'siswa' : 'ringkasan');

        $ringkasan = collect($rombel)->map(function ($r) use ($status) {
            $baris = $this->baris($r);
            $lengkap = collect($baris)->whereNotNull('semester');

            return [
                'id' => $r['id'], 'kelas' => $r['kelas'], 'siswa' => $r['jumlah_siswa'], 'tp' => count($r['tp']),
                'kelengkapan' => $status[$r['id']]['terisi'] ?? 0,
                'rata' => $lengkap->count() ? round($lengkap->avg('semester'), 1) : null,
                'status' => $status[$r['id']]['status'] ?? 'belum_mulai',
            ];
        })->all();

        $aktif = Guru::cariRombel($request->query('rombel'));
        $baris = collect($this->baris($aktif))
            ->filter(fn ($b) => Paginasi::cocok($request->query('q'), $b['nama'], $b['nisn']))->values();
        $semua = collect($this->baris($aktif));
        $lengkap = $semua->whereNotNull('semester');
        $nilaiKey = $periode === 'sts' ? 'sts' : 'semester';

        return view('guru.rekap-nilai', [
            'tab' => $tab,
            'kosong' => $request->query('state') === 'kosong',
            'periode' => $periode,
            'ringkasan' => $ringkasan,
            'aktif' => $aktif,
            'rombel' => $rombel,
            'jumlahSts' => intdiv(count($aktif['tp']), 2),
            'baris' => Paginasi::buat($baris, $request),
            'kktp' => self::KKTP,
            'statistik' => [
                'siswa' => $semua->count(),
                'rata' => $lengkap->count() ? round($lengkap->avg($nilaiKey), 1) : null,
                'kelengkapan' => $semua->count() ? (int) round($lengkap->count() / $semua->count() * 100) : 0,
                'dibawah' => $lengkap->filter(fn ($b) => $b[$nilaiKey] < self::KKTP)->count(),
            ],
        ]);
    }

    /** Baris DKN per siswa: nilai TP, nilai STS (rata-rata TP STS) dan semester (rata-rata semua TP). */
    private function baris(array $rombel): array
    {
        $n = count($rombel['tp']);
        $sts = intdiv($n, 2);

        return array_map(function ($p) use ($n, $sts) {
            $valid = fn ($v) => $v !== null && $v >= 0 && $v <= 100;
            $nilaiSts = array_slice($p['nilai'], 0, $sts);
            $lengkapSts = $sts && count(array_filter($nilaiSts, $valid)) === $sts;
            $lengkap = $n && count(array_filter($p['nilai'], $valid)) === $n;

            return [
                'nisn' => $p['nisn'], 'nama' => $p['nama'], 'nilai' => $p['nilai'],
                'sts' => $lengkapSts ? round(array_sum($nilaiSts) / $sts, 1) : null,
                'semester' => $lengkap ? round(array_sum($p['nilai']) / $n, 1) : null,
            ];
        }, Guru::peserta($rombel));
    }
}
