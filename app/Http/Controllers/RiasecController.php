<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RiasecController extends Controller
{
    // ==========================================
    // BACKEND UNTUK TAMPILAN SISWA
    // ==========================================

    // 1. Mengambil Daftar Soal RIASEC
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
                'dimensi_riasec.nama as nama_dimensi'
            )
            ->orderBy('soal_riasec.urutan', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $soal
        ]);
    }

    // 2. Menyimpan Jawaban Siswa & Menghitung Skor
    public function submit(Request $request)
    {
        $request->validate([
            'id_sesi_tes' => 'required|integer',
            'jawaban'     => 'required|array',
        ]);

        $idSesiTes = $request->id_sesi_tes;
        $jawabanList = $request->jawaban;

        DB::beginTransaction();
        try {
            // Hapus jawaban lama jika pernah diisi pada sesi ini
            DB::table('jawaban_riasec')->where('id_sesi_tes', $idSesiTes)->delete();
            DB::table('hasil_tes_riasec')->where('id_sesi_tes', $idSesiTes)->delete();

            $skorDimensi = [];

            foreach ($jawabanList as $item) {
                // Simpan tiap jawaban
                DB::table('jawaban_riasec')->insert([
                    'id_sesi_tes'    => $idSesiTes,
                    'id_soal_riasec' => $item['id_soal'],
                    'nilai'          => $item['nilai'],
                ]);

                // Akumulasi skor per dimensi
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

            // Simpan hasil akumulasi skor ke hasil_tes_riasec
            foreach ($skorDimensi as $idDimensi => $skorMentah) {
                DB::table('hasil_tes_riasec')->insert([
                    'id_sesi_tes' => $idSesiTes,
                    'id_dimensi'  => $idDimensi,
                    'skor_mentah' => $skorMentah,
                    'skor_normal' => $skorMentah,
                ]);
            }

            // Update waktu selesai tes pada sesi_tes
            DB::table('sesi_tes')
                ->where('id_sesi_tes', $idSesiTes)
                ->update(['waktu_selesai' => now()]);

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Jawaban berhasil disimpan dan hasil tes telah dihitung.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menyimpan jawaban: ' . $e->getMessage()
            ], 500);
        }
    }

    // 3. Menampilkan Hasil Tes Individual Siswa
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

    // ==========================================
    // BACKEND UNTUK DASHBOARD GURU BK
    // ==========================================

    // 4. Rekapitulasi Seluruh Siswa untuk Dashboard Guru BK
    public function dashboardGuru()
    {
        $rekapSiswa = DB::table('sesi_tes')
            ->join('riwayat_kelas', 'sesi_tes.id_riwayat_kelas', '=', 'riwayat_kelas.id_riwayat_kelas')
            ->join('siswa', 'riwayat_kelas.id_siswa', '=', 'siswa.id_siswa')
            ->leftJoin('hasil_tes_riasec', 'sesi_tes.id_sesi_tes', '=', 'hasil_tes_riasec.id_sesi_tes')
            ->select(
                'siswa.id_siswa',
                'siswa.nis',
                'siswa.nama as nama_siswa',
                'sesi_tes.id_sesi_tes',
                'sesi_tes.tingkat',
                'sesi_tes.waktu_selesai',
                DB::raw('COUNT(hasil_tes_riasec.id_tes_riasec) as total_dikerjakan')
            )
            ->groupBy('siswa.id_siswa', 'siswa.nis', 'siswa.nama', 'sesi_tes.id_sesi_tes', 'sesi_tes.tingkat', 'sesi_tes.waktu_selesai')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $rekapSiswa
        ]);
    }

    // 5. Detail Skor RIASEC Siswa Spesifik (Modal / Detail Guru BK)
    public function detailSiswa($idSesiTes)
    {
        $siswa = DB::table('sesi_tes')
            ->join('riwayat_kelas', 'sesi_tes.id_riwayat_kelas', '=', 'riwayat_kelas.id_riwayat_kelas')
            ->join('siswa', 'riwayat_kelas.id_siswa', '=', 'siswa.id_siswa')
            ->where('sesi_tes.id_sesi_tes', $idSesiTes)
            ->select('siswa.nama', 'siswa.nis', 'sesi_tes.tingkat')
            ->first();

        $hasil = DB::table('hasil_tes_riasec')
            ->join('dimensi_riasec', 'hasil_tes_riasec.id_dimensi', '=', 'dimensi_riasec.id_dimensi')
            ->where('hasil_tes_riasec.id_sesi_tes', $idSesiTes)
            ->select('dimensi_riasec.kode', 'dimensi_riasec.nama as dimensi', 'hasil_tes_riasec.skor_mentah')
            ->orderBy('hasil_tes_riasec.skor_mentah', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'siswa'  => $siswa,
            'hasil'  => $hasil
        ]);
    }
}