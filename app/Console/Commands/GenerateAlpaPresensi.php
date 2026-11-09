<?php

namespace App\Console\Commands;

use App\Models\Presensi;
use App\Models\Siswa;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateAlpaPresensi extends Command
{
    protected $signature = 'presensi:generate-alpa';

    protected $description = 'Membuat presensi Alpa untuk siswa yang belum melakukan presensi hari ini';

    public function handle(): int
    {
        $waktuSekarang = now('Asia/Jakarta');
        $tanggal = $waktuSekarang->toDateString();

        if ($waktuSekarang->format('H:i') < '07:31') {
            $this->warn('Presensi Alpa hanya dapat dibuat mulai pukul 07.31.');

            return self::FAILURE;
        }

        $daftarIdSiswa = Siswa::select('id_siswa')
            ->orderBy('id_siswa')
            ->pluck('id_siswa');

        $totalAlpa = 0;

        foreach ($daftarIdSiswa as $idSiswa) {
            $berhasilMembuatAlpa = DB::transaction(function () use (
                $idSiswa,
                $tanggal,
                $waktuSekarang
            ) {
                // Gunakan penguncian yang sama dengan PresensiController.
                $siswa = Siswa::where('id_siswa', $idSiswa)
                    ->lockForUpdate()
                    ->first();

                if (!$siswa) {
                    return false;
                }

                // Periksa ulang setelah mendapatkan lock.
                // Semua catatan presensi hari ini mencegah pembuatan Alpa.
                $sudahAdaPresensi = Presensi::where(
                    'id_siswa',
                    $siswa->id_siswa
                )
                    ->whereDate('tanggal', $tanggal)
                    ->exists();

                if ($sudahAdaPresensi) {
                    return false;
                }

                Presensi::create([
                    'id_siswa' => $siswa->id_siswa,
                    'metode' => null,
                    'status' => 'Alpa',
                    'hari' => $waktuSekarang->locale('id')->translatedFormat('l'),
                    'tanggal' => $tanggal,
                    'waktu_presensi' => null,
                ]);

                return true;
            });

            if ($berhasilMembuatAlpa) {
                $totalAlpa++;
            }
        }

        $this->info("Berhasil membuat {$totalAlpa} presensi Alpa.");

        return self::SUCCESS;
    }
}