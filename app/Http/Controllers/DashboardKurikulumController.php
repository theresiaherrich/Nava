<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardKurikulumController extends Controller
{
    private const PER_PAGE = 10;

    public function index(Request $request)
    {
        $tingkat = (string) $request->query('tingkat', 'semua');
        $q       = trim((string) $request->query('q', ''));
        $page    = max(1, (int) $request->query('page', 1));

        $namaPengguna   = optional($request->user())->name ?? 'Pengguna';
        $sapaan         = $this->sapaan();
        $tanggalHariIni = now()->locale('id')->isoFormat('dddd, D MMMM YYYY');

        // Periode aktif
        // ['nama' => 'Periode STS Ganjil 2026/2027 dibuka', 'keterangan' => 'Ditutup Jumat, 23 Oktober 2026.']
        $periode = null;

        // Statistik ringkas
        $stats = [
            'siswa_aktif'           => 0,
            'siswa_keterangan'      => '',
            'guru'                  => 0,
            'guru_keterangan'       => '',
            'rombel_tanpa_pengampu' => 0,
            'rombel_keterangan'     => '',
        ];

        // Progres penilaian STS per tingkat (nilai dalam persen, 0-100)
        // Format: ['tingkat' => 'X', 'terisi' => 82, 'terverifikasi' => 44, 'terkunci' => 30]
        $progres = collect(['X', 'XI', 'XII'])->map(fn ($t) => [
            'tingkat'       => $t,
            'terisi'        => 0,
            'terverifikasi' => 0,
            'terkunci'      => 0,
        ]);

        // Format satu baris kelas:
        // [
        //     'tingkat' => 'X', 'kelas' => 'X-A', 'wali_kelas' => 'Andini Pratiwi, S.Pd.',
        //     'mapel_disetujui' => 16, 'mapel_total' => 16,
        //     'verifikasi' => 'selesai',  // selesai | berjalan | belum
        //     'kunci'      => 'terbuka',  // terbuka | terkunci | koreksi
        //     'rapor'      => 'belum_dibuat', // belum_dibuat | pdf_dibuat | terbit
        //     'url'        => '#',
        // ]
        $semuaKelas = collect();

        $kelasFiltered = $semuaKelas
            ->when($tingkat !== 'semua', fn ($c) => $c->where('tingkat', $tingkat))
            ->when($q !== '', fn ($c) => $c->filter(fn ($k) =>
                str_contains(mb_strtolower($k['kelas'] ?? ''), mb_strtolower($q)) ||
                str_contains(mb_strtolower($k['wali_kelas'] ?? ''), mb_strtolower($q))
            ))
            ->values();

        $totalKelas = $kelasFiltered->count();
        $lastPage   = max(1, (int) ceil($totalKelas / self::PER_PAGE));
        $page       = min($page, $lastPage);
        $kelasRows  = $kelasFiltered->forPage($page, self::PER_PAGE)->values();

        // Perlu ditindaklanjuti
        // Format: ['judul' => '...', 'aksi' => 'Atur', 'url' => '#', 'tone' => 'orange|blue|purple']
        $perlu = collect();

        // Status rapor STS
        $statusRapor = [
            'total'       => 0,
            'siap'        => 0,
            'terkunci'    => 0,
            'terbit'      => 0,
            'belum_siap'  => 0,
        ];

        return view('dashboard-kurikulum.index', compact(
            'namaPengguna', 'sapaan', 'tanggalHariIni', 'periode', 'stats',
            'progres', 'kelasRows', 'tingkat', 'q', 'page', 'lastPage',
            'totalKelas', 'perlu', 'statusRapor'
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