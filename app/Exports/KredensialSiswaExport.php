<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class KredensialSiswaExport implements FromArray, WithHeadings
{
    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function headings(): array
    {
        return [
            'nama',
            'nis',
            'username',
            'password_awal',
        ];
    }

    public function array(): array
    {
        return $this->data;
    }
}