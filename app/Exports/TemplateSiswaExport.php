<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TemplateSiswaExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return [
            'nama',
            'nis',
            'nisn',
            'agama',
            'jenis_kelamin',
            'alamat',
            'tahun_masuk',
            'urutan_masuk',
        ];
    }

    public function array(): array
    {
        return [];
    }
}
