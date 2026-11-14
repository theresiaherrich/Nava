<?php

namespace App\Services;

use Illuminate\Support\Collection;

/** Alokasi berdasarkan posisi terurut; tidak membaca/menulis flag legacy. */
class AlokasiTpService
{
    public static function semua(Collection $tp): Collection
    {
        return $tp->sortBy('nomor_urut')->values();
    }

    public static function untukInput(Collection $tp, string $jenis): Collection
    {
        $semua = self::semua($tp);
        $batas = intdiv($semua->count(), 2);

        return ($jenis === 'STS' ? $semua->take($batas) : $semua->slice($batas))->values();
    }

    /** Kelengkapan paket dan perhitungan SAS membutuhkan SEMUA TP. */
    public static function untukRekap(Collection $tp, string $jenis): Collection
    {
        return $jenis === 'STS' ? self::untukInput($tp, $jenis) : self::semua($tp);
    }
}
