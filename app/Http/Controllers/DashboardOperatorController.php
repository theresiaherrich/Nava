<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardOperatorController extends Controller
{
    public function index(Request $request)
    {
        $namaPengguna   = optional($request->user())->name ?? 'Pengguna';
        $sapaan         = $this->sapaan();
        $tanggalHariIni = now()->locale('id')->isoFormat('dddd, D MMMM YYYY');

        // Statistik ringkas
        $stats = [
            'akun_aktif'        => 0,
            'siswa'             => 0,
            'siswa_keterangan'  => '',
            'guru'              => 0,
            'guru_keterangan'   => '',
        ];

        // Orang tua terhubung
        $ortu = [
            'terhubung' => 0,
            'total'     => 0,
            'belum'     => 0,
        ];

        // Perlu ditindaklanjuti
        // Format: ['judul' => '56 siswa belum terhubung ke akun orang tua', 'aksi' => 'Hubungkan', 'url' => '#', 'tone' => 'orange|blue|red']
        $perlu = collect();

        // Aktivitas terbaru
        // Format: ['judul' => 'Impor 960 siswa', 'detail' => 'data_siswa_2026.xlsx', 'waktu' => '1 Sep 2026, 09.14', 'tone' => 'green|blue|purple|gray']
        $aktivitas = collect();

        return view('dashboard-operator.index', compact(
            'namaPengguna', 'sapaan', 'tanggalHariIni', 'stats', 'ortu', 'perlu', 'aktivitas'
        ));
    }

    private function sapaan(): string
    {
        $jam = now()->hour;

        return match (true) {
            $jam < 11 => 'pagi',
            $jam < 15 => 'siang',
            $jam < 18 => 'sore',
            default   => 'malam',
        };
    }
}