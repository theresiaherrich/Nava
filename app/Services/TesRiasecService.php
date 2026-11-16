<?php

namespace App\Services;

use App\Models\HasilTesRiasec;
use App\Models\JawabanRiasec;
use App\Models\RiwayatKelas;
use App\Models\SesiTes;
use App\Models\SoalRiasec;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * TesRiasecService — Logika pengerjaan dan penyimpanan tes RIASEC.
 *
 * Sesuai ATURAN_A3.md:
 * - Tidak ada periode tes — tes bisa dilakukan kapan saja selama semester aktif.
 * - Tes boleh dilakukan berkali-kali (tidak ada batasan 1x).
 * - skor_mentah = jumlah nilai jawaban per dimensi
 * - skor_normal = (skor_mentah - n) / (4n) * 100  [n = jumlah soal dimensi]
 * - Jawaban disimpan ke jawaban_riasec (per soal).
 * - Skor disimpan ke hasil_tes_riasec (per dimensi), BUKAN inline di sesi_tes.
 * - Tie-break urutan kode dominan: R > I > A > S > E > C.
 */
class TesRiasecService
{
    private const URUTAN_RIASEC = ['R', 'I', 'A', 'S', 'E', 'C'];

    /**
     * Ambil semua soal aktif untuk pengerjaan tes.
     */
    public function getSoalAktif(): Collection
    {
        return SoalRiasec::with('dimensi')->aktif()->get();
    }

    /**
     * Hitung skor_mentah dan skor_normal per dimensi dari array jawaban.
     *
     * @param  array  $jawaban  [id_soal_riasec => nilai (1-5)]
     * @return array  ['R' => ['mentah' => int, 'normal' => float], ...]
     */
    public function hitungSkor(array $jawaban): array
    {
        $soalDimensi = SoalRiasec::with('dimensi')
            ->aktif()
            ->get()
            ->mapWithKeys(fn ($s) => [$s->id_soal_riasec => $s->dimensi->kode ?? null]);

        // Hitung sum dan jumlah soal per dimensi
        $sum   = array_fill_keys(self::URUTAN_RIASEC, 0);
        $count = array_fill_keys(self::URUTAN_RIASEC, 0);

        foreach ($jawaban as $idSoal => $nilai) {
            $kode = $soalDimensi[$idSoal] ?? null;
            if ($kode && isset($sum[$kode])) {
                $sum[$kode]   += (int) $nilai;
                $count[$kode] += 1;
            }
        }

        // Hitung skor_mentah dan skor_normal
        $hasil = [];
        foreach (self::URUTAN_RIASEC as $kode) {
            $n       = $count[$kode];
            $mentah  = $sum[$kode];
            // skor_normal = (mentah - n) / (4n) * 100; jika n=0, skor=0
            $normal  = $n > 0 ? round(($mentah - $n) / (4 * $n) * 100, 2) : 0.0;
            $hasil[$kode] = [
                'mentah' => $mentah,
                'normal' => max(0.0, $normal), // pastikan tidak negatif
            ];
        }

        return $hasil;
    }

    /**
     * Tentukan kode dominan 3 huruf berdasarkan skor_normal tertinggi.
     * Tie-break: urutan R > I > A > S > E > C.
     *
     * @param  array  $skor  ['R' => ['mentah'=>..., 'normal'=>...], ...]
     */
    public function kodeDominan(array $skor): string
    {
        // Urutkan berdasarkan skor_normal DESC, lalu urutan RIASEC ASC untuk tie-break
        $kodeUrutan = array_flip(self::URUTAN_RIASEC);

        $kodeList = self::URUTAN_RIASEC;
        usort($kodeList, function ($a, $b) use ($skor, $kodeUrutan) {
            $selisih = $skor[$b]['normal'] <=> $skor[$a]['normal'];
            if ($selisih !== 0) return $selisih;
            return $kodeUrutan[$a] <=> $kodeUrutan[$b]; // tie-break
        });

        return implode('', array_slice($kodeList, 0, 3));
    }

    /**
     * Simpan sesi tes, jawaban per soal, dan skor per dimensi dalam 1 transaksi.
     *
     * @param  RiwayatKelas  $riwayat
     * @param  array         $jawaban  [id_soal_riasec => nilai]
     * @param  array         $skor     ['R' => ['mentah'=>int,'normal'=>float], ...]
     * @param  string        $tingkat  'X' | 'XII'
     */
    public function simpanHasil(
        RiwayatKelas $riwayat,
        array $jawaban,
        array $skor,
        string $tingkat
    ): SesiTes {
        return DB::transaction(function () use ($riwayat, $jawaban, $skor, $tingkat) {

            // 1. Buat sesi tes
            $sesi = SesiTes::create([
                'id_riwayat_kelas' => $riwayat->id_riwayat_kelas,
                'tingkat'          => $tingkat,
                'kode_holland'     => $this->kodeDominan($skor),
                'waktu_mulai'      => now(),
                'waktu_selesai'    => now(),
                'status'           => 'selesai',
            ]);

            // 2. Simpan jawaban per soal → jawaban_riasec
            $jawabanInsert = [];
            foreach ($jawaban as $idSoal => $nilai) {
                $jawabanInsert[] = [
                    'id_sesi_tes'    => $sesi->id_sesi_tes,
                    'id_soal_riasec' => $idSoal,
                    'nilai'          => (int) $nilai,
                ];
            }
            JawabanRiasec::insert($jawabanInsert);

            // 3. Simpan skor per dimensi → hasil_tes_riasec
            // Ambil mapping kode → id_dimensi
            $dimensiMap = \App\Models\DimensiRiasec::pluck('id_dimensi', 'kode');

            $hasilInsert = [];
            foreach ($skor as $kode => $skorData) {
                $idDimensi = $dimensiMap[$kode] ?? null;
                if ($idDimensi === null) continue;

                $hasilInsert[] = [
                    'id_sesi_tes' => $sesi->id_sesi_tes,
                    'id_dimensi'  => $idDimensi,
                    'skor_mentah' => $skorData['mentah'],
                    'skor_normal' => $skorData['normal'],
                ];
            }
            HasilTesRiasec::insert($hasilInsert);

            return $sesi;
        });
    }

    /**
     * Cek apakah siswa sudah punya sesi tes 'selesai' di riwayat kelas ini.
     * Sesuai ATURAN_A3: tidak ada batasan jumlah tes. Method ini hanya untuk
     * keperluan informasi (misal tampilkan status di dashboard), BUKAN untuk blokir.
     */
    public function sudahTes(RiwayatKelas $riwayat): bool
    {
        return SesiTes::where('id_riwayat_kelas', $riwayat->id_riwayat_kelas)
            ->where('status', 'selesai')
            ->exists();
    }

    /**
     * Ambil sesi tes terakhir yang selesai untuk riwayat kelas ini.
     */
    public function sesiTesTerakhir(RiwayatKelas $riwayat): ?SesiTes
    {
        return SesiTes::where('id_riwayat_kelas', $riwayat->id_riwayat_kelas)
            ->where('status', 'selesai')
            ->orderByDesc('waktu_selesai')
            ->with(['hasilTes.dimensi'])
            ->first();
    }
}
