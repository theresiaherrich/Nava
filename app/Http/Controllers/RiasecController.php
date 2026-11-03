<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RiasecController extends Controller
{
    // 1. Menampilkan Halaman / Data Soal RIASEC
    public function index()
    {
        $soal = DB::table('soal_riasec')
            ->join('dimensi_riasec', 'soal_riasec.id_dimensi', '=', 'dimensi_riasec.id_dimensi')
            ->select(
                'soal_riasec.id_soal_riasec',
                'soal_riasec.teks',
                'soal_riasec.urutan',
                'dimensi_riasec.id_dimensi',
                'dimensi_riasec.kode',
                'dimensi_riasec.nama'
            )
            ->orderBy('soal_riasec.urutan', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $soal
        ]);
    }

    // 2. Menyimpan Jawaban & Menghitung Hasil Tes
    public function submit(Request $request)
    {
        $request->validate([
            'id_sesi_tes' => 'required|integer',
            'jawaban'     => 'required|array', // Format: [['id_soal' => 1, 'nilai' => 4], ...]
        ]);

        $idSesiTes = $request->id_sesi_tes;
        $jawabanList = $request->jawaban;

        // Array untuk menampung total skor mentah per dimensi
        $skorDimensi = [];

        foreach ($jawabanList as $item) {
            // Simpan jawaban ke tabel jawaban_riasec
            DB::table('jawaban_riasec')->insert([
                'id_sesi_tes'    => $idSesiTes,
                'id_soal_riasec' => $item['id_soal'],
                'nilai'          => $item['nilai'],
            ]);

            // Cari tahu id_dimensi dari soal ini
            $soal = DB::table('soal_riasec')
                ->where('id_soal_riasec', $item['id_soal'])
                ->first();

            if ($soal) {
                if (!isset($skorDimensi[$soal->id_dimensi])) {
                    $skorDimensi[$soal->id_dimensi] = 0;
                }
                $skorDimensi[$soal->id_dimensi] += $item['nilai'];
            }
        }

        // Simpan akumulasi skor mentah ke tabel hasil_tes_riasec
        foreach ($skorDimensi as $idDimensi => $skorMentah) {
            DB::table('hasil_tes_riasec')->insert([
                'id_sesi_tes' => $idSesiTes,
                'id_dimensi'  => $idDimensi,
                'skor_mentah' => $skorMentah,
                'skor_normal' => $skorMentah, // Bisa disesuaikan jika ada rumus pembagian/persentase
            ]);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Jawaban berhasil disimpan dan hasil tes telah dihitung.'
        ]);
    }

    // 3. Menampilkan Hasil Tes Berdasarkan Sesi
    public function hasil($idSesiTes)
    {
        $hasil = DB::table('hasil_tes_riasec')
            ->join('dimensi_riasec', 'hasil_tes_riasec.id_dimensi', '=', 'dimensi_riasec.id_dimensi')
            ->where('hasil_tes_riasec.id_sesi_tes', $idSesiTes)
            ->select(
                'dimensi_riasec.kode',
                'dimensi_riasec.nama',
                'hasil_tes_riasec.skor_mentah',
                'hasil_tes_riasec.skor_normal'
            )
            ->orderBy('hasil_tes_riasec.skor_mentah', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $hasil
        ]);
    }
}