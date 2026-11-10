<?php

namespace App\Http\Controllers;

class BerandaAkademikController extends Controller
{
    public function index()
    {
        // Data kosong dulu. Nanti diisi dari database.

        // Data siswa:
        // ['nama' => 'Citra Lestari', 'nis' => '2526003', 'nisn' => '0081234567',
        //  'kelas' => 'X-B', 'fase' => 'E', 'wali_kelas' => 'Siti Rahmawati, S.Pd.']
        $siswa = null;

        // Status rapor (true jika sudah terbit)
        $statusSts = [
            'terbit'     => false,
            'nama'       => 'Rapor STS Ganjil 2026/2027',
            'tanggal'    => null,   // '30 Okt 2026'
            'waktu'      => null,   // '09.00'
            'keterangan' => 'Diterbitkan oleh sekolah',
        ];

        $statusSemester = [
            'terbit'     => false,
            'nama'       => 'Rapor Semester Ganjil 2026/2027',
            'tanggal'    => null,
            'keterangan' => 'Diterbitkan setelah 18 Des 2026',
        ];

        // Format satu mapel:
        // ['nama' => 'Matematika', 'nilai' => 86, 'kktp' => 75, 'status' => 'tercapai']
        // status: 'tercapai' atau 'perlu_penguatan'
        $mapels = collect();

        $totalMapel     = $mapels->count();
        $tercapai       = $mapels->where('status', 'tercapai')->count();
        $perluPenguatan = $totalMapel - $tercapai;
        $rataRata       = $totalMapel ? round($mapels->avg('nilai'), 1) : null;
        $namaPerlu      = $mapels->where('status', '!=', 'tercapai')->pluck('nama')->implode(' · ');

        $tanggalHariIni = now()->locale('id')->isoFormat('dddd, D MMMM YYYY');

        return view('beranda-akademik.index', compact(
            'siswa',
            'statusSts',
            'statusSemester',
            'mapels',
            'totalMapel',
            'tercapai',
            'perluPenguatan',
            'rataRata',
            'namaPerlu',
            'tanggalHariIni'
        ));
    }
}