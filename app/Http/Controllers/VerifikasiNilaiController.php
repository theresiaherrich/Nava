<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VerifikasiNilaiController extends Controller
{
    public function index(Request $request)
    {
        return view('verifikasi-nilai.index', $this->dataHalaman());
    }

    public function show(Request $request, $mapel)
    {
        return view('verifikasi-nilai.index', $this->dataHalaman($mapel));
    }

    public function requestRevision(Request $request, $mapel)
    {
        // TODO: simpan revisi, catat riwayat, lalu kembali ke halaman
        return back();
    }

    public function approve(Request $request, $mapel)
    {
        // TODO: simpan persetujuan, catat riwayat, lalu kembali ke halaman
        return back();
    }

    /**
     * Susun data untuk view.
     * TODO: ganti setiap nilai kosong dengan query database.
     */
    private function dataHalaman($mapelId = null): array
    {
        return [
            'daftarMapel'   => collect(),   // mata pelajaran di kelas wali
            'mapelAktif'    => null,        // isi dengan mapel yang dipilih / pertama
            'kolomTp'       => [],          // mis. ['TP1', 'TP2', 'TP3', 'TP4', 'TP5']
            'siswa'         => collect(),   // siswa beserta nilai_tp, sas, nilai_rapor
            'statistik'     => [
                'kelengkapan'     => 0,
                'jumlah_terisi'   => 0,
                'jumlah_siswa'    => 0,
                'rata_rata'       => '-',
                'rentang'         => '-',
                'perlu_diperiksa' => 0,
            ],
            'riwayat'       => collect(),   // riwayat verifikasi
            'periodeAktif'  => null,        // periode yang sedang dibuka
            'periodeList'   => [],          // daftar periode
            'kelas'         => null,        // mis. 'X-B'
            'kelasTerkunci' => false,
            'kunciAt'       => null,
        ];
    }
}