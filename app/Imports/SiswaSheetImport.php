<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\Import;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/** Hanya membaca sheet pertama dengan baris judul; validasi ada di SiswaImportService. */
class SiswaSheetImport implements Import, WithHeadingRow {}
