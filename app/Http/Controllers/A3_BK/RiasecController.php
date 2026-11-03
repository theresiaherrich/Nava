<?php

namespace App\Http\Controllers\A3_BK;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RiasecController extends Controller
{
    /**
     * Dashboard Guru BK (Mockup Image 2)
     */
    public function dashboardGuru()
    {
        $stats = [
            'total_siswa'   => 960,
            'sudah_tes'     => 742,
            'belum_tes'     => 218,
            'pengajuan_aktif' => 3,
        ];

        return view('bk.dashboard_guru', compact('stats'));
    }

    /**
     * Dashboard Siswa Modul A3 (Mockup Image 1)
     */
    public function dashboardSiswa()
    {
        $siswa = [
            'nama' => 'Nadia Azzahra Putri',
            'kelas' => 'X-3',
            'nis' => '2410073',
            'status_tes' => 'Selesai',
            'tgl_tes' => '18 Agustus 2025',
            'kode_dominan' => 'IAS',
            'skor_tertinggi' => 91,
            'rekomendasi_paket' => 3,
        ];

        $skorRiasec = [
            'R' => 72,
            'I' => 91,
            'A' => 84,
            'S' => 76,
            'E' => 65,
            'C' => 58,
        ];

        return view('bk.dashboard_siswa', compact('siswa', 'skorRiasec'));
    }

    /**
     * Mengambil daftar soal RIASEC untuk halaman pengerjaan tes.
     */
    public function getSoal()
    {
        $soal = DB::table('soal_riasec')
            ->join('dimensi_riasec', 'soal_riasec.id_dimensi', '=', 'dimensi_riasec.id_dimensi')
            ->select('soal_riasec.id_soal_riasec', 'soal_riasec.teks', 'soal_riasec.urutan', 'dimensi_riasec.kode as dimensi')
            ->orderBy('soal_riasec.urutan', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $soal,
        ]);
    }

    /**
     * Submit jawaban tes RIASEC dan hitung skor dominan.
     */
    public function submit(Request $request)
    {
        $request->validate([
            'id_sesi_tes' => 'required',
            'jawaban'     => 'required|array',
        ]);

        $idSesiTes = $request->id_sesi_tes;
        $jawaban = $request->jawaban; // [id_soal => nilai (1-5)]

        // Simpan setiap jawaban ke tabel jawaban_riasec
        foreach ($jawaban as $idSoal => $nilai) {
            DB::table('jawaban_riasec')->updateOrInsert(
                ['id_sesi_tes' => $idSesiTes, 'id_soal_riasec' => $idSoal],
                ['nilai' => $nilai]
            );
        }

        // Ambil pemetaan soal ke dimensi
        $soalDimensi = DB::table('soal_riasec')
            ->join('dimensi_riasec', 'soal_riasec.id_dimensi', '=', 'dimensi_riasec.id_dimensi')
            ->pluck('dimensi_riasec.kode', 'soal_riasec.id_soal_riasec');

        $dimensiMap = DB::table('dimensi_riasec')->pluck('id_dimensi', 'kode');

        // Hitung total skor per dimensi
        $skorDimensi = ['R' => 0, 'I' => 0, 'A' => 0, 'S' => 0, 'E' => 0, 'C' => 0];
        $countDimensi = ['R' => 0, 'I' => 0, 'A' => 0, 'S' => 0, 'E' => 0, 'C' => 0];

        foreach ($jawaban as $idSoal => $nilai) {
            $kode = $soalDimensi[$idSoal] ?? null;
            if ($kode && isset($skorDimensi[$kode])) {
                $skorDimensi[$kode] += $nilai;
                $countDimensi[$kode]++;
            }
        }

        // Simpan ke tabel hasil_tes_riasec
        foreach ($skorDimensi as $kode => $total) {
            if (isset($dimensiMap[$kode])) {
                $idDimensi = $dimensiMap[$kode];
                $maxPossible = ($countDimensi[$kode] > 0 ? $countDimensi[$kode] * 5 : 1);
                $persentase = round(($total / $maxPossible) * 100, 2);

                DB::table('hasil_tes_riasec')->updateOrInsert(
                    ['id_sesi_tes' => $idSesiTes, 'id_dimensi' => $idDimensi],
                    ['skor_mentah' => $total, 'skor_normal' => $persentase]
                );
            }
        }

        // Tentukan 3 kode dominan Holland (Top 3)
        arsort($skorDimensi);
        $top3 = array_slice(array_keys($skorDimensi), 0, 3);
        $kodeHolland = implode('', $top3);

        DB::table('sesi_tes')
            ->where('id_sesi_tes', $idSesiTes)
            ->update([
                'kode_holland' => $kodeHolland,
                'waktu_selesai' => now(),
            ]);

        return response()->json([
            'status' => 'success',
            'kode_holland' => $kodeHolland,
            'skor' => $skorDimensi,
            'redirect_url' => url('/riasec/hasil-view/' . $idSesiTes),
        ]);
    }
}
