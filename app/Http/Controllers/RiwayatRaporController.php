<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RiwayatRaporController extends Controller
{
    public function index(Request $request)
    {
        $tahun = (string) $request->query('tahun', 'semua');

        // Data kosong dulu. Nanti diisi dari database.
        // Format satu baris riwayat:
        // [
        //     'tahun_ajaran'   => '2026/2027',
        //     'semester'       => 'Ganjil',
        //     'jenis'          => 'Rapor STS',        // atau 'Rapor Semester'
        //     'kelas'          => 'X-B',
        //     'tanggal_terbit' => '30 Okt 2026',       // null jika belum terbit
        //     'status'         => 'terbit',            // atau 'belum_terbit'
        //     'versi'          => 1,                   // > 1 berarti pernah dikoreksi
        //     'tanggal_update' => null,                // '2 Nov 2026' jika versi > 1
        //     'url'            => null,                // link PDF, null jika belum ada
        // ]
        $semua = collect();

        $tahunOpsi = collect(['semua'])
            ->merge($semua->pluck('tahun_ajaran')->filter()->unique())
            ->values();

        $riwayat = $tahun === 'semua'
            ? $semua
            : $semua->where('tahun_ajaran', $tahun)->values();

        return view('riwayat-rapor.index', compact('riwayat', 'tahunOpsi', 'tahun'));
    }
}