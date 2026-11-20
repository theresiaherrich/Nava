<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SiswaImport implements ToCollection, WithHeadingRow
{
    public Collection $rows;
    public array $usernames = [];

    public function collection(Collection $rows): void
    {
    $this->rows = $rows;
    }

    public function getRows(): Collection
    {
    return $this->rows;
    }

    public function validateHeaders(): bool
    {
    $requiredHeaders = [
        'nama',
        'nis',
        'tahun_masuk',
        'urutan_masuk',
    ];

    return collect($requiredHeaders)->every(
        fn ($header) => array_key_exists($header, $this->rows->first()?->toArray() ?? [])
    );
    }

    public function validateRows(): array
    {
    $errors = [];
    $seenNis = [];
    $seenNisn = [];
    $seenUsername = [];

    foreach ($this->rows as $index => $row) {
        $data = $row->toArray();
        $rowNumber = $index + 2;

        if (empty(trim((string) ($data['nama'] ?? '')))) {
            $errors[] = "Baris {$rowNumber}: Nama wajib diisi.";
        }

        if (strlen(trim((string) ($data['nama'] ?? ''))) > 120) {
            $errors[] = "Baris {$rowNumber}: Nama maksimal 120 karakter.";
        }

        $nis = trim((string) ($data['nis'] ?? ''));
        if (strlen($nis) > 30) {
            $errors[] = "Baris {$rowNumber}: NIS maksimal 30 karakter.";
        }

        if ($nis === '') {
            $errors[] = "Baris {$rowNumber}: NIS wajib diisi.";
        } else {
            if (in_array($nis, $seenNis, true)) {
                $errors[] = "Baris {$rowNumber}: NIS {$nis} duplikat dalam file Excel.";
            } else {
                $seenNis[] = $nis;
            }

            if (\App\Models\Siswa::where('nis', $nis)->exists()) {
                $errors[] = "Baris {$rowNumber}: NIS {$nis} sudah terdaftar.";
            }
        }

        $nisn = trim((string) ($data['nisn'] ?? ''));
        if (strlen($nisn) > 30) {
            $errors[] = "Baris {$rowNumber}: NISN maksimal 30 karakter.";
        }

        if ($nisn !== '') {
            if (in_array($nisn, $seenNisn, true)) {
                $errors[] = "Baris {$rowNumber}: NISN {$nisn} duplikat dalam file Excel.";
            } else {
                $seenNisn[] = $nisn;
            }

            if (\App\Models\Siswa::where('nisn', $nisn)->exists()) {
                $errors[] = "Baris {$rowNumber}: NISN {$nisn} sudah terdaftar.";
            }
        }

        if (
            !empty($data['jenis_kelamin']) &&
            !in_array(strtoupper(trim((string) $data['jenis_kelamin'])), ['L', 'P'], true)
        ) {
            $errors[] = "Baris {$rowNumber}: Jenis kelamin hanya boleh L atau P.";
        }

        if (strlen(trim((string) ($data['agama'] ?? ''))) > 30) {
            $errors[] = "Baris {$rowNumber}: Agama maksimal 30 karakter.";
        }

        $tahunMasuk = $data['tahun_masuk'] ?? null;

        if (
            !is_numeric($tahunMasuk) ||
            $tahunMasuk < 1900 ||
            $tahunMasuk > 2100
        ) {
            $errors[] = "Baris {$rowNumber}: Tahun masuk harus berupa angka antara 1900–2100.";
        }

        $urutanMasuk = $data['urutan_masuk'] ?? null;

        if (
            !is_numeric($urutanMasuk) ||
            (float) $urutanMasuk != (int) $urutanMasuk ||
            $urutanMasuk < 1
        ) {
            $errors[] = "Baris {$rowNumber}: Urutan masuk harus berupa bilangan bulat lebih dari 0.";
        }

        if (
            !empty($data['nama']) &&
            is_numeric($tahunMasuk) &&
            is_numeric($urutanMasuk) &&
            (int) $urutanMasuk > 0
        ) {
            $namaDepan = explode(' ', trim($data['nama']))[0];

            $username = strtolower(
                preg_replace('/[^a-zA-Z0-9]/', '', $namaDepan)
            );

            $username .= (int) $tahunMasuk;

            $username .= str_pad(
                (string) (int) $urutanMasuk,
                3,
                '0',
                STR_PAD_LEFT
            );

            $this->usernames[$index] = $username;

            if (in_array($username, $seenUsername, true)) {
                $errors[] = "Baris {$rowNumber}: Username {$username} duplikat dalam file Excel.";
            } else {
                $seenUsername[] = $username;
            }

            if (\App\Models\User::where('username', $username)->exists()) {
                $errors[] = "Baris {$rowNumber}: Username {$username} sudah digunakan.";
            }
        }
    }

    return $errors;
    }
}
