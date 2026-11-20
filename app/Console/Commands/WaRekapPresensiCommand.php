<?php
namespace App\Console\Commands;

use App\Services\WaPenerima;
use App\Services\WaPengirim;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class WaRekapPresensiCommand extends Command
{
    protected $signature = 'wa:rekap-presensi
        {--kirim : benar-benar mengirim pesan}
        {--siswa= : batasi ke satu siswa}';

    protected $description = 'Kirim rekap presensi mingguan kepada orang tua';

    public function handle(WaPengirim $pengirim): int
    {
        $awal = now('Asia/Jakarta')->startOfWeek(Carbon::MONDAY);
        $akhir = now('Asia/Jakarta')->endOfWeek(Carbon::SUNDAY);

        $periode = $awal->format('d/m/Y') . ' - ' . $akhir->format('d/m/Y');
        $rujukan = $awal->format('o-\WW');

        $q = DB::table('presensi as p')
            ->join('siswa as s', 's.id_siswa', '=', 'p.id_siswa')
            ->whereBetween('p.tanggal', [
                $awal->toDateString(),
                min($akhir, now('Asia/Jakarta'))->toDateString(),
            ])
            ->groupBy('s.id_siswa', 's.nama')
            ->selectRaw("
                s.id_siswa,
                s.nama,
                SUM(p.status = 'Hadir') as hadir,
                SUM(p.status = 'Terlambat') as terlambat,
                SUM(p.status = 'Sakit') as sakit,
                SUM(p.status = 'Izin') as izin,
                SUM(p.status = 'Alpa') as alpa
            ");

        if ($this->option('siswa')) {
            $q->where('s.id_siswa', (int) $this->option('siswa'));
        }

        $rows = $q->get();

        $this->info("Jumlah siswa dalam rekap: {$rows->count()}");
        $this->info("Periode: {$periode}");

        foreach ($rows as $r) {
            $kunci = "rekap-presensi-{$r->id_siswa}-{$rujukan}";

            $data = [
                'nama_siswa' => $r->nama,
                'periode' => $periode,
                'hadir' => (int) $r->hadir,
                'terlambat' => (int) $r->terlambat,
                'sakit' => (int) $r->sakit,
                'izin' => (int) $r->izin,
                'alpa' => (int) $r->alpa,
            ];

            if (!$this->option('kirim')) {
                $this->line("- DRY-RUN: {$r->nama} | " . json_encode($data));
                continue;
            }

            $p = WaPenerima::untukSiswa((int) $r->id_siswa);

            if ($p === null) {
                $this->warn("- {$r->nama}: tidak ada penerima layak");
                continue;
            }

            $hasil = $pengirim->kirim(
                (int) $r->id_siswa,
                'presensi_mingguan',
                $data + ['nama_ortu' => $p['nama']],
                $kunci
            );

            $this->line("- {$r->nama}: {$hasil['hasil']}");
        }

        return self::SUCCESS;
    }
}
