<?php

namespace App\Http\Controllers\Wali;

use App\Http\Controllers\Controller;
use App\Support\Dummy\Rapor;
use App\Support\Dummy\Wali;
use Illuminate\Http\Request;

class CetakRaporController extends Controller
{
    /**
     * Cetak & Ekspor Rapor wali kelas (desain Wali 5.01, 5.03–5.07).
     * Varian: ?state=belum-kunci (5.01), ?cari=cit (5.04), ?proses=1 (5.05), ?jenis=semester (5.06), ?tab=riwayat (5.07).
     * TODO API: GET /api/wali/rapor?jenis=, GET /api/rapor/{id}/pdf, POST /api/wali/rapor/kelas/pdf (job + progres), GET /api/wali/rapor/riwayat
     */
    public function index(Request $request)
    {
        $terkunci = $request->query('state') !== 'belum-kunci';
        $pelengkap = collect(Wali::pelengkap('terkunci'))->keyBy('nisn');

        $siswa = array_map(fn ($s) => [
            'nis' => $pelengkap[$s['nisn']]['nis'], 'nisn' => $s['nisn'], 'nama' => $s['nama'], 'kelas' => Wali::KELAS,
        ], Wali::siswa());

        $dokumen = collect($siswa)->mapWithKeys(function ($s) use ($pelengkap) {
            $p = $pelengkap[$s['nisn']];
            $semester = Rapor::dokumen($s, 'semester');
            // Rapor semester memakai pelengkap rapor wali kelas (catatan, ekskul, kehadiran A1).
            $semester['catatan'] = $p['catatan'];
            $semester['absen'] = $p['kehadiran'];
            $semester['ekskul'] = array_map(fn ($e) => ['nama' => $e[0], 'predikat' => $e[1], 'ket' => $e[1] === 'SB' ? 'Sangat aktif dalam kegiatan' : 'Aktif mengikuti latihan'], $p['ekskul']);

            return [$s['nis'] => ['sts' => Rapor::dokumen($s, 'sts'), 'semester' => $semester]];
        })->all();

        return view('wali.cetak-rapor', [
            'kelas' => Wali::KELAS,
            'terkunci' => $terkunci,
            'dikunci' => '26 Okt 2026',
            'menunggu' => collect(Wali::paket())->where('status', 'menunggu')->count(),
            'siswa' => $siswa,
            'dokumen' => $dokumen,
            'riwayat' => $terkunci ? [
                ['waktu' => '30 Okt 2026, 09.12', 'jenis' => 'Rapor STS', 'cakupan' => 'kelas', 'versi' => 'v2 · Koreksi', 'oleh' => 'Siti Rahmawati'],
                ['waktu' => '30 Okt 2026, 09.10', 'jenis' => 'Rapor STS', 'cakupan' => 'Citra Lestari', 'versi' => 'v2 · Koreksi', 'oleh' => 'Siti Rahmawati'],
                ['waktu' => '27 Okt 2026, 14.05', 'jenis' => 'Rapor STS', 'cakupan' => 'kelas', 'versi' => 'v1', 'oleh' => 'Siti Rahmawati'],
                ['waktu' => '27 Okt 2026, 13.58', 'jenis' => 'Rapor STS', 'cakupan' => 'Bagas Pratama', 'versi' => 'v1', 'oleh' => 'Siti Rahmawati'],
            ] : [],
        ]);
    }
}
