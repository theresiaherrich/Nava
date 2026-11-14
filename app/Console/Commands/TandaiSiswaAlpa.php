<?php

namespace App\Console\Commands;

use App\Models\Presensi;
use App\Models\Siswa;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TandaiSiswaAlpa extends Command
{
    protected $signature = 'app:tandai-siswa-alpa';

    protected $description = 'Mencatat status Alpa untuk siswa yang belum presensi hari ini.';

    public function handle(): int
    {
        $waktuSekarang = now('Asia/Jakarta');

        // Pencatatan Alpa dimulai pukul 07.30 WIB.
        if ($waktuSekarang->format('H:i') < '07:30') {
            $this->warn('Pencatatan Alpa belum memasuki waktunya.');

            return self::SUCCESS;
        }

        $tanggal = $waktuSekarang->toDateString();
        $jumlahAlpa = 0;

        Siswa::query()->chunkById(
            100,
            function ($siswas) use ($tanggal, &$jumlahAlpa) {
                foreach ($siswas as $siswa) {
                    DB::transaction(function () use (
                        $siswa,
                        $tanggal,
                        &$jumlahAlpa
                    ) {
                        $siswaTerkunci = Siswa::where(
                            'id_siswa',
                            $siswa->id_siswa
                        )
                            ->lockForUpdate()
                            ->first();

                        if (!$siswaTerkunci) {
                            return;
                        }

                        // Periksa seluruh catatan presensi pada tanggal tersebut.
                        $sudahAdaCatatan = Presensi::where(
                            'id_siswa',
                            $siswaTerkunci->id_siswa
                        )
                            ->whereDate('tanggal', $tanggal)
                            ->exists();

                        // Buat Alpa hanya jika siswa belum memiliki catatan presensi.
                        if (!$sudahAdaCatatan) {
                            Presensi::create([
                                'id_siswa' => $siswaTerkunci->id_siswa,
                                'metode' => null,
                                'status' => 'Alpa',
                                'tanggal' => $tanggal,
                                'waktu_presensi' => null,
                            ]);

                            $jumlahAlpa++;
                        }
                    });
                }
            },
            'id_siswa'
        );

        $this->info("Berhasil mencatat {$jumlahAlpa} siswa sebagai Alpa.");

        return self::SUCCESS;
    }
}