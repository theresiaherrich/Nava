<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithTitle;

/** Rekap nilai satu rombel: baris siswa, kolom TP + STS/SAS/akhir. Sel kosong = belum diisi. */
class RekapRombelExport implements FromArray, WithHeadings, WithStrictNullComparison, WithTitle
{
    public function __construct(private Collection $tp, private array $baris) {}

    public function title(): string
    {
        return 'Rekap';
    }

    public function headings(): array
    {
        return ['NIS', 'Nama', 'Kelas', ...$this->tp->map(fn ($t) => 'TP'.$t->urutan)->all(), 'Nilai STS', 'Nilai SAS', 'Nilai Akhir', 'TP Terisi'];
    }

    public function array(): array
    {
        return array_map(fn ($b) => [
            "\u{200B}".$b['siswa']['nis'], // pertahankan nol di depan & cegah formula
            self::aman($b['siswa']['nama']),
            $b['kelas'],
            ...$this->tp->map(fn ($t) => $b['nilai_tp'][$t->urutan] ?? null)->all(),
            $b['nilai_sts'], $b['nilai_sas'], $b['nilai_akhir'],
            "{$b['jumlah_terisi']}/{$b['jumlah_tp']}",
        ], $this->baris);
    }

    /** Cegah formula injection ketika nama diawali =, +, -, @. */
    public static function aman(?string $s): ?string
    {
        return $s !== null && preg_match('/^[=+\-@]/', $s) ? "'".$s : $s;
    }
}
