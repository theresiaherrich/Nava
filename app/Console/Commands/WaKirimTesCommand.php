<?php

namespace App\Console\Commands;

use App\Services\WaPengirim;
use App\Services\WaPenerima;
use Illuminate\Console\Command;

class WaKirimTesCommand extends Command
{
    protected $signature = 'wa:kirim-tes
        {id_siswa}
        {template : nama template di whatsapp-service/src/templates.js}
        {--data= : JSON data template, contoh: {"nama":"Raditya"}}
        {--kirim : benar-benar kirim (tanpa ini hanya dry-run)}';

    protected $description = 'Tes kirim notifikasi WA ke penerima satu siswa';

    public function handle(WaPengirim $pengirim): int
    {
        $idSiswa  = (int) $this->argument('id_siswa');
        $template = $this->argument('template');
        $data     = json_decode($this->option('data') ?: '{}', true);

        if (!is_array($data)) {
            $this->error('--data bukan JSON yang valid.');
            return self::FAILURE;
        }

        $p = WaPenerima::untukSiswa($idSiswa);
        if ($p === null) {
            $this->warn('Tidak ada penerima layak.');
            return self::SUCCESS;
        }

        $this->line("Penerima: {$p['hubungan']} {$p['nama']} -> " . WaPenerima::samarkan($p['nomor']));

        if (!$this->option('kirim')) {
            $this->info('DRY-RUN: tidak ada yang dikirim. Tambahkan --kirim untuk mengirim.');
            return self::SUCCESS;
        }

        $kunci = 'tes-' . $idSiswa . '-' . now()->format('YmdHis');
        $r = $pengirim->kirim($idSiswa, $template, $data, $kunci);

        $this->info('Hasil: ' . $r['hasil'] . (isset($r['error']) ? " ({$r['error']})" : ''));
        return $r['hasil'] === 'diterima' ? self::SUCCESS : self::FAILURE;
    }
}