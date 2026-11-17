<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Support\Dummy\Guru;
use Illuminate\Http\Request;

class InputNilaiController extends Controller
{
    /**
     * Input Nilai (desain Guru 19–29).
     * Varian: ?rombel=mtl-1 (campuran), ?periode=sts, ?state=gagal|terkirim|revisi|ditutup|terkunci,
     *         ?modal=tempel|kirim, ?pilih-rombel=1.
     */
    public function index(Request $request)
    {
        $semua = [...Guru::rombel(), Guru::rombelCampuran()];
        $aktif = collect($semua)->firstWhere('id', $request->query('rombel'))
            ?? collect($semua)->firstWhere('id', 'mtk-x-b');
        $state = in_array($request->query('state'), ['gagal', 'terkirim', 'revisi', 'ditutup', 'terkunci'], true) ? $request->query('state') : 'draf';

        return view('guru.input-nilai', [
            'rombel' => collect($semua)->map(fn ($r) => collect($r)->except('tp')->all() + ['jumlah_tp' => count($r['tp'])])->all(),
            'aktif' => $aktif,
            'peserta' => Guru::peserta($aktif),
            'singkat' => Guru::SINGKAT,
            'periode' => $request->query('periode') === 'sts' ? 'sts' : 'semester',
            'state' => $state,
            // Catatan revisi dari wali kelas (desain 27). TODO API: GET /api/guru/paket/{id}/riwayat
            'revisi' => ['oleh' => 'wali kelas X-B', 'waktu' => '13 Okt 2026, 13.20', 'catatan' => 'Nilai TP3 atas nama Dimas Saputra tertulis 8, mohon dicek apakah seharusnya 80.', 'nisn' => '0081234564', 'tp' => 2, 'nilai' => 8],
        ]);
    }
}
