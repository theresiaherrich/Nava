<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PresensiController extends Controller
{
    public function kelola()
    {
        $role = strtolower(request('role', 'wali_kelas'));

        $siswa = [
            [
                'nama' => 'Mutiara Bintang',
                'nisn' => '245150600',
                'kelas' => 'XI MIPA 2',
                'status' => 'Telat',
                'jam' => '07:28',
                'metode' => 'QR Code',
                'berkas' => '-',
                'keterangan' => 'Terlambat masuk',
            ],
            // Data siswa lainnya bisa dimasukkan di sini.
        ];

        return view('presensi.kelola.index', compact('role', 'siswa'));
    }
}