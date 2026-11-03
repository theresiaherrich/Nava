<?php

namespace App\Http\Controllers\A3_BK;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RiasecController extends Controller
{
    public function dashboardGuru()
    {
        // 1. Data Dummy / Query Stat Dashboard Guru BK
        $stats = [
            'total_siswa'   => 960,
            'sudah_tes'     => 742,
            'belum_tes'     => 218,
            'sesi_aktif'    => 3,
        ];

        // 2. Sample Data Rekap Tes RIASEC
        $rekapTes = DB::table('sesi_tes')
            ->select('sesi_tes.*')
            ->limit(5)
            ->get();

        return view('bk.dashboard_guru', compact('stats', 'rekapTes'));
    }

    public function simpanSesi(Request $request)
    {
        $request->validate([
            'nama_sesi'    => 'required|string|max:255',
            'jenjang'      => 'required|string',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date',
            'kuota'        => 'required|integer',
        ]);

        // Logic simpan ke database tabel sesi_tes
        DB::table('sesi_tes')->insert([
            'nama_sesi'       => $request->nama_sesi,
            'jenjang'         => $request->jenjang,
            'tanggal_mulai'   => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'kuota'           => $request->kuota,
            'status'          => 'Aktif',
            'created_at'      => now(),
        ]);

        return redirect()->back()->with('success', 'Sesi tes RIASEC berhasil ditambahkan.');
    }
}