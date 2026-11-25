<?php

namespace App\Services;

use App\Exceptions\AturanBisnisException;
use App\Imports\SiswaSheetImport;
use App\Models\Agama;
use App\Models\Kelas;
use App\Models\RiwayatKelas;
use App\Models\Semester;
use App\Models\Siswa;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

/**
 * Impor siswa dua langkah: pratinjau (validasi, tidak menyimpan) → simpan dengan token.
 * Baris valid disimpan sementara di cache milik pengguna pengunggah.
 */
class SiswaImportService
{
    public const KOLOM = ['nis', 'nisn', 'nama', 'jenis_kelamin', 'agama', 'tempat_lahir', 'tanggal_lahir', 'alamat', 'tahun_masuk', 'kelas'];

    public function __construct(private AuditService $audit) {}

    /** @return array{token:?string, total:int, valid:int, gagal: list<array{baris:int, errors: array}>, contoh: array} */
    public function pratinjau(UploadedFile $file): array
    {
        $sheet = Excel::toArray(new SiswaSheetImport, $file)[0] ?? [];
        $maks = (int) config('siakad.impor.maks_baris');

        if (count($sheet) === 0) {
            throw new AturanBisnisException('File kosong atau baris judul tidak sesuai template.', 'IMPORT_EMPTY');
        }
        if (count($sheet) > $maks) {
            throw new AturanBisnisException("Maksimal {$maks} baris per impor.", 'IMPORT_TOO_LARGE');
        }

        $agama = Agama::pluck('id_agama', 'nama')->mapWithKeys(fn ($id, $n) => [Str::lower($n) => $id]);
        $semester = Semester::aktif();
        $kelas = $semester
            ? Kelas::where('id_tahun_ajaran', $semester->id_tahun_ajaran)->pluck('id_kelas', 'nama')->mapWithKeys(fn ($id, $n) => [Str::upper($n) => $id])
            : collect();
        $nisAda = Siswa::pluck('nis')->flip();
        $nisnAda = Siswa::whereNotNull('nisn')->pluck('nisn')->flip();

        $valid = [];
        $gagal = [];
        $nisFile = [];
        $nisnFile = [];

        foreach ($sheet as $i => $raw) {
            $baris = $i + 2; // baris 1 = judul
            $row = $this->normalisasi($raw);
            if (array_filter($row, fn ($v) => $v !== null) === []) {
                continue; // baris kosong
            }

            $v = Validator::make($row, [
                'nis' => ['required', 'string', 'max:30'],
                'nisn' => ['nullable', 'digits:10'],
                'nama' => ['required', 'string', 'max:120'],
                'jenis_kelamin' => ['nullable', 'in:L,P'],
                'tanggal_lahir' => ['nullable', 'date'],
                'tahun_masuk' => ['nullable', 'integer', 'between:2000,2100'],
                'tempat_lahir' => ['nullable', 'max:60'],
                'alamat' => ['nullable', 'max:500'],
            ]);
            $errors = $v->errors()->toArray();

            if ($row['nis'] !== null && ($nisAda->has($row['nis']) || isset($nisFile[$row['nis']]))) {
                $errors['nis'][] = 'NIS sudah terdaftar atau ganda di file.';
            }
            if ($row['nisn'] !== null && ($nisnAda->has($row['nisn']) || isset($nisnFile[$row['nisn']]))) {
                $errors['nisn'][] = 'NISN sudah terdaftar atau ganda di file.';
            }
            if ($row['agama'] !== null && ! $agama->has(Str::lower($row['agama']))) {
                $errors['agama'][] = 'Agama tidak dikenal.';
            }
            if ($row['kelas'] !== null && ! $kelas->has(Str::upper($row['kelas']))) {
                $errors['kelas'][] = $semester ? 'Kelas tidak ada di tahun ajaran aktif.' : 'Belum ada semester aktif untuk penempatan kelas.';
            }

            if ($errors !== []) {
                $gagal[] = ['baris' => $baris, 'nis' => $row['nis'], 'nama' => $row['nama'], 'errors' => $errors];

                continue;
            }

            $nisFile[$row['nis']] = true;
            if ($row['nisn'] !== null) {
                $nisnFile[$row['nisn']] = true;
            }
            $valid[] = Arr::except($row, ['agama', 'kelas']) + [
                'baris' => $baris,
                'id_agama' => $row['agama'] !== null ? $agama[Str::lower($row['agama'])] : null,
                'id_kelas' => $row['kelas'] !== null ? $kelas[Str::upper($row['kelas'])] : null,
            ];
        }

        $token = null;
        if ($valid !== []) {
            $token = (string) Str::uuid();
            Cache::put($this->kunci($token), ['id_user' => Auth::id(), 'baris' => $valid, 'id_semester' => $semester?->id_semester],
                now()->addMinutes((int) config('siakad.impor.pratinjau_ttl_menit')));
        }

        return [
            'token' => $token,
            'total' => count($valid) + count($gagal),
            'valid' => count($valid),
            'gagal' => $gagal,
            'contoh' => array_slice($valid, 0, 20),
        ];
    }

    /** @return array{dibuat:int, ditempatkan:int, gagal: list<array{baris:int, errors: array}>} */
    public function simpan(string $token): array
    {
        $data = Cache::pull($this->kunci($token));
        if (! $data || (int) $data['id_user'] !== (int) Auth::id()) {
            throw new AturanBisnisException('Pratinjau impor tidak ditemukan atau kedaluwarsa. Unggah ulang file.', 'IMPORT_TOKEN_INVALID', 404);
        }

        return DB::transaction(function () use ($data) {
            $dibuat = 0;
            $ditempatkan = 0;
            $gagal = [];

            foreach ($data['baris'] as $row) {
                // Cek ulang: data bisa berubah sejak pratinjau.
                if (Siswa::where('nis', $row['nis'])->orWhere(fn ($q) => $row['nisn'] ? $q->where('nisn', $row['nisn']) : $q->whereRaw('1=0'))->exists()) {
                    $gagal[] = ['baris' => $row['baris'], 'errors' => ['nis' => ['NIS/NISN sudah terdaftar.']]];

                    continue;
                }
                $siswa = Siswa::create(Arr::only($row, ['nis', 'nisn', 'nama', 'jenis_kelamin', 'id_agama', 'tempat_lahir', 'tanggal_lahir', 'alamat', 'tahun_masuk']));
                $dibuat++;

                if ($row['id_kelas'] && $data['id_semester']) {
                    RiwayatKelas::create(['id_siswa' => $siswa->id_siswa, 'id_kelas' => $row['id_kelas'], 'id_semester' => $data['id_semester']]);
                    $ditempatkan++;
                }
            }

            $this->audit->catat('impor_siswa', 'siswa', null, null, ['dibuat' => $dibuat, 'ditempatkan' => $ditempatkan, 'gagal' => count($gagal)]);

            return ['dibuat' => $dibuat, 'ditempatkan' => $ditempatkan, 'gagal' => $gagal];
        });
    }

    private function normalisasi(array $raw): array
    {
        $row = [];
        foreach (self::KOLOM as $k) {
            $v = $raw[$k] ?? null;
            if (is_float($v) && floor($v) === $v) {
                $v = (string) (int) $v; // angka dari Excel: 1001.0 → "1001"
            }
            $v = is_string($v) || is_int($v) ? trim((string) $v) : $v;
            $row[$k] = $v === '' ? null : $v;
        }

        if (is_numeric($row['tanggal_lahir']) && ! str_contains((string) $row['tanggal_lahir'], '-')) {
            $row['tanggal_lahir'] = ExcelDate::excelToDateTimeObject((float) $row['tanggal_lahir'])->format('Y-m-d');
        }
        if ($row['jenis_kelamin'] !== null) {
            $row['jenis_kelamin'] = Str::upper(substr((string) $row['jenis_kelamin'], 0, 1));
        }

        return $row;
    }

    private function kunci(string $token): string
    {
        return 'impor-siswa:'.$token;
    }
}
