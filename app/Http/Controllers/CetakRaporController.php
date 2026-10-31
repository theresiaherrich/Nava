<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class CetakRaporController extends Controller
{
    public function index(Request $request)
    {
        return view('cetak-rapor.index', $this->dataHalaman($request));
    }

    /**
     * Unduh PDF satu siswa.
     */
    public function unduhSiswa(Request $request, $siswa)
    {
        // TODO: generate PDF rapor untuk siswa $siswa
        return back();
    }

    /**
     * Unduh PDF satu kelas (ZIP berisi PDF per siswa).
     */
    public function unduhKelas(Request $request)
    {
        // TODO: buat job generate PDF seluruh kelas, lalu tampilkan progres
        return back();
    }

    /**
     * Unduh ulang versi rapor yang sudah pernah dicetak.
     */
    public function unduhUlang(Request $request, $riwayat)
    {
        // TODO: ambil file hasil cetak dari riwayat $riwayat
        return back();
    }

    /**
     * Susun data untuk view.
     * TODO: ganti setiap nilai kosong dengan query database.
     */
    private function dataHalaman(Request $request): array
    {
        return [
            'kelas'                => null,                       // mis. 'X-B'
            'tab'                  => $request->query('tab', 'pratinjau'),
            'periodeAktif'         => $request->query('periode', 'sts'),
            'dikunci'              => false,
            'dikunciAt'            => null,                       // mis. '26 Okt 2026'
            'mapelBelumVerifikasi' => 0,

            // Semua siswa, dipakai untuk pencarian. Kolom: id, nisn, nama, status ('siap'|'belum')
            'siswaSemua'           => collect(),

            // Siswa per halaman untuk sidebar (10 per halaman)
            'siswaPage'            => new LengthAwarePaginator(
                collect(),
                0,
                10,
                $request->integer('page', 1),
                ['path' => $request->url(), 'query' => $request->query()]
            ),

            'totalSiswa'           => 0,
            'siapCount'            => 0,

            // Siswa yang sedang dipratinjau (objek: id, nisn, nama) atau null
            'siswaAktif'           => null,

            // Data rapor siswa aktif, atau null. Bentuk:
            // [
            //   'jenis' => 'sts'|'semester', 'judul' => 'SUMATIF TENGAH SEMESTER',
            //   'tahun_ajaran' => '2026/2027', 'sekolah' => 'SMA NEGERI 1 KOTA MALANG',
            //   'alamat' => '...', 'nama' => '...', 'nisn' => '...', 'kelas' => 'X-B',
            //   'fase' => 'E', 'tanggal_ttd' => '30 Oktober 2026',
            //   'wali_nama' => '...', 'wali_nip' => '...',
            //   'kolom_tp' => ['TP1', 'TP2', 'TP3', 'TP4'],   // hanya untuk STS
            //   'mapel' => [['nama' => '...', 'tp' => [88, 86, 90, 97], 'nilai_akhir' => 88, 'capaian' => '...']],
            // ]
            'rapor'                => null,

            // Daftar halaman untuk Rapor Semester (satu item per halaman berisi baris mapel)
            'halamanRapor'         => [],
            'halamanAktif'         => $request->integer('halaman', 1),

            // Riwayat cetak. Kolom: id, tanggal, jenis, versi, dicetak_oleh
            'riwayat'              => collect(),

            // Progres atau hasil cetak kelas, atau null.
            // ['status' => 'proses', 'label' => 'Rapor STS X-B', 'selesai' => 16, 'total' => 32, 'estimasi' => '+5 detik']
            // ['status' => 'selesai', 'total' => 32, 'nama_file' => '...zip', 'ukuran' => '4,2 MB', 'zip_url' => '#', 'gabung_url' => '#']
            'jobCetak'             => null,
        ];
    }
}