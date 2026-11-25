<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;

/** Semua sel ditulis sebagai teks agar NISN/NIP berawalan nol tidak berubah. */
class KredensialExport extends StringValueBinder implements FromArray, WithCustomValueBinder, WithHeadings
{
    /** @param list<array{username:string,password:string,nama:string,profil_id:int}> $baris */
    public function __construct(private array $baris) {}

    public function headings(): array
    {
        return ['Nama', 'Username', 'Kata sandi awal', 'ID profil'];
    }

    public function array(): array
    {
        return array_map(fn ($b) => [$b['nama'], $b['username'], $b['password'], $b['profil_id']], $this->baris);
    }
}
