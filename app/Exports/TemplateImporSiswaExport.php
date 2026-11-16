<?php

namespace App\Exports;

use App\Services\SiswaImportService;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;

/** Template impor; kolom ditulis sebagai teks supaya NIS/NISN berawalan nol aman. */
class TemplateImporSiswaExport extends StringValueBinder implements FromArray, WithCustomValueBinder, WithHeadings
{
    public function headings(): array
    {
        return SiswaImportService::KOLOM;
    }

    public function array(): array
    {
        return [['1001', '0081234561', 'Contoh Siswa', 'L', 'Islam', 'Malang', '2010-05-17', 'Jl. Contoh 1', '2026', 'X-A']];
    }
}
