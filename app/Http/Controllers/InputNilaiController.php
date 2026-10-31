<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class InputNilaiController extends Controller
{
    public function index(): View
    {
        // TODO: ganti dengan query database
        return view('input-nilai.index', [
            'namaSekolah' => 'SMA Negeri 1 Kota Malang',
            'rombelList'  => [], // daftar rombel yang diampu: id, kelas, mapel, status, statusLabel, disabled
            'rombelAktif' => null, // rombel yang sedang dipilih
            'students'    => [], // siswa: nisn, nama, nilai (array per TP)
        ]);
    }
}