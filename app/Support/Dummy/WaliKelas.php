<?php

namespace App\Support\Dummy;

/**
 * Data dummy Penetapan Wali Kelas (desain Kurikulum 4.10).
 */
class WaliKelas
{
    /** TODO API: GET /api/master/guru (calon wali kelas) */
    public static function guru(): array
    {
        return [
            'Dra. Endang Lestari', 'Hadi Santoso, S.Pd.', 'Nur Aini, S.Si.', 'Agus Prasetyo, S.Pd.', 'Ahmad Fauzi, S.Ag.',
            'Bambang Sutrisno, S.Pd.', 'Wahyu Setiawan, S.Pd.', 'Rina Marlina, S.Sos.', 'Dewi Kusuma, S.E.',
            'Maria Hartati, S.Pd.', 'Fransiska Wulan, M.Pd.', 'Siti Rahmawati, S.Pd.', 'Andini Pertiwi, S.Pd.',
            'Rudi Hartono, M.Pd.', 'Dewi Anggraini, S.Pd.', 'Lin Mei, S.Pd.', 'Yohanes Setiawan, S.Th.',
            'Sri Wahyuni, S.Pd.', 'Budi Santoso, S.Pd.', 'Teguh Prakoso, M.Pd.', 'Ratna Sari, S.Pd.',
            'Eko Purnomo, S.Kom.', 'Lilis Suryani, S.Pd.', 'Hendro Wibowo, S.Pd.', 'Puji Lestari, S.Pd.',
            'Anton Wijaya, S.Pd.', 'Nining Kurniasih, S.Pd.', 'Joko Widodo, S.Pd.', 'Indra Gunawan, S.Pd.',
            'Yuliana, S.Pd.', 'Galih Saputra, S.Pd.', 'Mega Puspita, S.Pd.', 'Rizal Fahmi, S.Pd.',
            'Novi Andriani, S.Pd.', 'Kartika Dewi, S.Pd.',
        ];
    }

    /** TODO API: GET /api/kurikulum/wali-kelas?semester= */
    public static function kelas(): array
    {
        $wali = self::waliPerKelas();
        $kelas = [];
        foreach (['X', 'XI', 'XII'] as $t) {
            foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'] as $k) {
                $nama = "$t-$k";
                $kelas[] = ['id_kelas' => strtolower($nama), 'kelas' => $nama, 'tingkat' => $t, 'jumlah_siswa' => 32, 'wali' => $wali[$nama] ?? null];
            }
        }

        return $kelas;
    }

    /** Wali kelas tersimpan per kelas (XI-D sengaja kosong, sesuai desain). */
    public static function waliPerKelas(): array
    {
        $guru = self::guru();

        $wali = [
            'XI-A' => 'Dra. Endang Lestari', 'XI-B' => 'Hadi Santoso, S.Pd.', 'XI-C' => 'Nur Aini, S.Si.',
            'XI-E' => 'Maria Hartati, S.Pd.', 'XI-F' => 'Ahmad Fauzi, S.Ag.', 'XI-G' => 'Fransiska Wulan, M.Pd.',
            'XI-H' => 'Wahyu Setiawan, S.Pd.', 'XI-I' => 'Rina Marlina, S.Sos.', 'XI-J' => 'Dewi Kusuma, S.E.',
            'X-A' => 'Andini Pertiwi, S.Pd.', 'X-B' => 'Siti Rahmawati, S.Pd.',
        ];

        // Kelas lain diisi guru yang belum menjadi wali.
        $sisa = array_values(array_diff($guru, $wali, ['Agus Prasetyo, S.Pd.', 'Bambang Sutrisno, S.Pd.']));
        $i = 0;
        foreach (['X', 'XII'] as $t) {
            foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'] as $k) {
                $wali["$t-$k"] ??= $sisa[$i++] ?? null;
            }
        }

        return $wali;
    }

    /** Perubahan belum disimpan untuk preview ?state=diubah (desain 4.10). */
    public static function contohPerubahan(): array
    {
        return ['XI-E' => 'Agus Prasetyo, S.Pd.', 'XI-G' => 'Bambang Sutrisno, S.Pd.'];
    }
}
