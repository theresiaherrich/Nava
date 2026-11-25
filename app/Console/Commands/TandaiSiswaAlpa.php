<?php

namespace App\Console\Commands;

use App\Models\Presensi;
use App\Models\Semester;
use App\Models\Siswa;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TandaiSiswaAlpa extends Command
{
    protected $signature = 'app:tandai-siswa-alpa';

    protected $description = 'Mencatat status Alpa untuk siswa yang belum presensi pada semester aktif.';

    public function handle(): int
    {
        $waktuSekarang = now('Asia/Jakarta');

        // Pencatatan Alpa dimulai pukul 07.30 WIB.
        if ($waktuSekarang->format('H:i') < '07:30') {
            $this->warn('Pencatatan Alpa belum memasuki waktunya.');

            return self::SUCCESS;
        }

        // Ambil semester yang aktif pada tahun ajaran aktif.
        $semesterAktif = Semester::query()
            ->where('aktif', true)
            ->whereHas('tahunAjaran', function ($query) {
                $query->where('aktif', true);
            })
            ->get(['id_semester']);

        // Pastikan hanya ada satu semester aktif.
        if ($semesterAktif->count() !== 1) {
            $this->error(
                'Pencatatan Alpa dibatalkan. Pastikan terdapat tepat satu semester aktif pada tahun ajaran aktif.'
            );

            return self::FAILURE;
        }

        $idSemesterAktif = $semesterAktif->first()->id_semester;
        $tanggal = $waktuSekarang->toDateString();
        $jumlahAlpa = 0;

        // Hanya proses siswa yang terdaftar pada semester aktif.
        Siswa::query()
            ->whereHas('riwayatKelas', function ($query) use ($idSemesterAktif) {
                $query->where('id_semester', $idSemesterAktif);
            })
            ->chunkById(
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

                            // Periksa apakah siswa sudah memiliki catatan presensi.
                            $sudahAdaCatatan = Presensi::where(
                                'id_siswa',
                                $siswaTerkunci->id_siswa
                            )
                                ->where('tanggal', $tanggal)
                                ->exists();

                            // Buat Alpa hanya jika belum ada catatan presensi.
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

        $this->info(
            "Berhasil mencatat {$jumlahAlpa} siswa sebagai Alpa."
        );

        return self::SUCCESS;
    }
}
