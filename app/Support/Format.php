<?php

namespace App\Support;

class Format
{
    /** 2010 → "2.010" */
    public static function angka(int|float|null $n, int $desimal = 0): string
    {
        return number_format((float) $n, $desimal, ',', '.');
    }
}
