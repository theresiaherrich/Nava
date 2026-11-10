<?php

namespace App\Services;

use App\Models\PeriodeTes;
use App\Models\RiwayatKelas;
use App\Models\SesiTes;
use App\Models\SoalRiasec;
use Illuminate\Database\Eloquent\Collection;

/**
 * TesRiasecService — logika pengerjaan dan penyimpanan tes RIASEC.
 */
class TesRiasecService
{
    /**
     * Ambil soal aktif untuk pengerjaan tes.
     * Urutan: id_soal_riasec ASC.
     */
    public function getSoalAktif(): Collection
    {
        return SoalRiasec::with('dimensi')->aktif()->get();
    }

    /**
     * Hitung skor per dimensi dari array jawaban.
     *
     * @param  array  $jawaban  [id_soal_riasec => nilai (1-5)]
     * @return array ['R' => float, 'I' => float, ..., 'C' => float]
     */
    public function hitungSkor(array $jawaban): array
    {
        // Ambil mapping soal → kode dimensi
        $soalDimensi = SoalRiasec::with('dimensi')
            ->aktif()
            ->get()
            ->pluck('dimensi.kode', 'id_soal_riasec');

        // Hitung raw sum dan count per dimensi
        $sum = ['R' => 0, 'I' => 0, 'A' => 0, 'S' => 0, 'E' => 0, 'C' => 0];
        $count = ['R' => 0, 'I' => 0, 'A' => 0, 'S' => 0, 'E' => 0, 'C' => 0];

        foreach ($jawaban as $idSoal => $nilai) {
            $kode = $soalDimensi[$idSoal] ?? null;
            if ($kode && array_key_exists($kode, $sum)) {
                $sum[$kode] += (int) $nilai;
                $count[$kode]++;
            }
        }

        // Hitung persentase: sum / (count * 5) * 100
        $skor = [];
        foreach ($sum as $kode => $total) {
            $maxPossible = $count[$kode] > 0 ? $count[$kode] * 5 : 1;
            $skor[$kode] = round($total / $maxPossible * 100, 2);
        }

        return $skor;
    }

    /**
     * Tentukan kode Holland 3 huruf (3 dimensi dengan skor tertinggi).
     */
    public function kodeHolland(array $skor): string
    {
        arsort($skor);

        return implode('', array_slice(array_keys($skor), 0, 3));
    }

    /**
     * Simpan hasil tes ke sesi_tes.
     * Trigger database akan menolak INSERT jika tidak ada periode aktif.
     *
     * @throws \Exception jika periode tidak aktif atau tes sudah ada
     */
    public function simpanHasil(RiwayatKelas $riwayat, array $skor): SesiTes
    {
        return SesiTes::create([
            'id_riwayat_kelas' => $riwayat->id_riwayat_kelas,
            'skor_r' => $skor['R'],
            'skor_i' => $skor['I'],
            'skor_a' => $skor['A'],
            'skor_s' => $skor['S'],
            'skor_e' => $skor['E'],
            'skor_c' => $skor['C'],
            'kode_holland' => $this->kodeHolland($skor),
            'waktu_selesai' => now(),
        ]);
    }

    /**
     * Cek apakah ada periode tes aktif untuk riwayat kelas ini.
     */
    public function periodeAktifAda(RiwayatKelas $riwayat): bool
    {
        return PeriodeTes::where('id_semester', $riwayat->id_semester)
            ->where('tingkat', $riwayat->kelas->tingkat)
            ->where('status', 'aktif')
            ->exists();
    }

    /**
     * Cek apakah siswa sudah mengerjakan tes di semester ini.
     */
    public function sudahTes(RiwayatKelas $riwayat): bool
    {
        return SesiTes::where('id_riwayat_kelas', $riwayat->id_riwayat_kelas)->exists();
    }
}
