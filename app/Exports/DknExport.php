<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithTitle;

class DknExport implements FromArray, WithHeadings, WithStrictNullComparison, WithTitle
{
    public function __construct(private array $dkn) {}

    public function title(): string
    {
        return 'DKN '.$this->dkn['kelas']['nama'];
    }

    public function headings(): array
    {
        return ['No', 'NIS', 'Nama', ...array_map(fn ($m) => $m['mapel'], $this->dkn['mapel']->all()), 'Jumlah', 'Rata-rata', 'Peringkat'];
    }

    public function array(): array
    {
        return $this->dkn['siswa']->values()->map(fn ($s, $i) => [
            $i + 1,
            "\u{200B}".$s['siswa']['nis'],
            RekapRombelExport::aman($s['siswa']['nama']),
            ...$this->dkn['mapel']->map(fn ($m) => $s['nilai'][$m['id_rombel']] ?? null)->all(),
            $s['jumlah'], $s['rata_rata'], $s['peringkat'],
        ])->all();
    }
}
