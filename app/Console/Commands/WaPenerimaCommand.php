<?php

namespace App\Console\Commands;

use App\Services\WaPenerima;
use Illuminate\Console\Command;

class WaPenerimaCommand extends Command
{
    protected $signature = 'wa:penerima {id_siswa : id_siswa dari tabel siswa}';
    protected $description = 'Tampilkan penerima notifikasi WA untuk satu siswa (tidak mengirim apa pun)';

    public function handle(): int
    {
        $p = WaPenerima::untukSiswa((int) $this->argument('id_siswa'));

        if ($p === null) {
            $this->warn('Tidak ada penerima layak (butuh orang tua verified dengan nomor valid).');
            return self::SUCCESS;
        }

        $this->info("{$p['hubungan']} {$p['nama']} -> " . WaPenerima::samarkan($p['nomor']));
        return self::SUCCESS;
    }
}