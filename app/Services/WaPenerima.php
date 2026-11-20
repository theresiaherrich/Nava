<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class WaPenerima
{
    /** Urutan prioritas: kalau ada Ibu yang valid, Ibu yang dipilih. */
    private const URUTAN = ['Ibu', 'Ayah', 'Wali'];

    /**
     * Ubah nomor apa pun (08..., +62 8..., 0815-1503-...) jadi 628xxxxxxxxx.
     * Mengembalikan null kalau bukan nomor seluler Indonesia yang masuk akal.
     */
    public static function normalisasiNomor(?string $nomor): ?string
    {
        if ($nomor === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $nomor);

        if ($digits === '' || $digits === null) {
            return null;
        }

        if (str_starts_with($digits, '62')) {
            $n = $digits;
        } elseif (str_starts_with($digits, '0')) {
            $n = '62' . substr($digits, 1);
        } elseif (str_starts_with($digits, '8')) {
            $n = '62' . $digits;
        } else {
            return null;
        }

        return preg_match('/^628\d{8,11}$/', $n) ? $n : null;
    }

    /**
     * Dari daftar baris orang_tua milik satu siswa, pilih satu penerima.
     * Fungsi murni (tanpa database) supaya mudah dites.
     *
     * @param  iterable $rows  baris dengan hubungan, nama, no_telepon, status_verifikasi
     * @return array|null      ['hubungan','nama','nomor'] atau null kalau tidak ada yang layak
     */
    public static function pilih(iterable $rows): ?array
    {
        $layak = [];

        foreach ($rows as $row) {
            $row = (object) $row;

            if (($row->status_verifikasi ?? null) !== 'verified') {
                continue;
            }

            $nomor = self::normalisasiNomor($row->no_telepon ?? null);
            if ($nomor === null) {
                continue;
            }

            $layak[$row->hubungan] = [
                'hubungan' => $row->hubungan,
                'nama'     => $row->nama ?? '',
                'nomor'    => $nomor,
            ];
        }

        foreach (self::URUTAN as $hubungan) {
            if (isset($layak[$hubungan])) {
                return $layak[$hubungan];
            }
        }

        return null;
    }

    /** Cari penerima notifikasi untuk satu siswa. */
    public static function untukSiswa(int $idSiswa): ?array
    {
        $rows = DB::table('orang_tua')
            ->where('id_siswa', $idSiswa)
            ->where('status_verifikasi', 'verified')
            ->whereNotNull('no_telepon')
            ->get(['hubungan', 'nama', 'no_telepon', 'status_verifikasi']);

        return self::pilih($rows);
    }

    /** Untuk log: 6281515038315 -> 62815****8315 */
    public static function samarkan(string $nomor): string
    {
        return substr($nomor, 0, 5) . '****' . substr($nomor, -4);
    }
}