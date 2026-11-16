<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

class RekapNilaiExport extends DefaultValueBinder implements FromCollection, WithColumnFormatting, WithCustomValueBinder, WithHeadings, WithStrictNullComparison
{
    public function __construct(private Collection $rows)
    {
        // Periksa sebelum keyBy(), yang dapat menimpa nomor duplikat,
        // dan sebelum file Excel sementara dibuat.
        foreach ($rows as $row) {
            $nomorTerpakai = [];
            foreach ($row['nilai_tp'] as $tp) {
                $nomor = $tp['nomor_urut'];
                if (! is_int($nomor) || $nomor < 1 || $nomor > 10 || isset($nomorTerpakai[$nomor])) {
                    throw ValidationException::withMessages([
                        'tp' => 'Ekspor dibatalkan: nomor TP pada rombel '.$row['rombel']['id'].' harus unik dan berada pada rentang 1–10. Periksa data TP.',
                    ]);
                }
                $nomorTerpakai[$nomor] = true;
            }
        }
    }

    public function headings(): array
    {
        return ['ID Peserta Rombel', 'NIS', 'Nama', 'Kelas', 'Rombel', 'Mapel',
            ...array_map(fn ($n) => 'TP '.$n, range(1, 10)),
            'Jumlah TP', 'TP Terisi', 'Lengkap', 'Status Paket', 'Nilai Akhir', 'Alasan Nilai Akhir'];
    }

    public function collection(): Collection
    {
        return $this->rows->map(function ($row) {
            $scores = collect($row['nilai_tp'])->keyBy('nomor_urut');

            return [$row['siswa_rombel_id'], (string) $row['siswa']['nis'], $row['siswa']['nama'],
                $row['kelas']['nama'], $row['rombel']['nama'], $row['mapel']['nama'],
                ...array_map(fn ($n) => isset($scores[$n]['skor']) ? (float) $scores[$n]['skor'] : null, range(1, 10)),
                $row['jumlah_tp'], $row['jumlah_tp_terisi'], $row['lengkap'] ? 'Ya' : 'Tidak',
                $row['status_paket'], $row['nilai_akhir'] === null ? null : (float) $row['nilai_akhir'], $row['alasan_nilai_akhir']];
        });
    }

    public function columnFormats(): array
    {
        return ['U' => '0.000000'];
    }

    public function bindValue(Cell $cell, mixed $value): bool
    {
        if (is_string($value)) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }
}
