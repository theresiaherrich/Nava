<?php

namespace App\Console\Commands;

use App\Services\WaPenerima;
use App\Services\WaPengirim;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class WaKirimPresensiCommand extends Command
{
    protected $signature = 'wa:kirim-presensi
        {--hari= : hanya presensi N hari terakhir (default config wa.jendela_hari)}
        {--siswa= : batasi ke satu id_siswa}
        {--status=Alpa,Terlambat : status yang diberitahukan, pisah koma}
        {--maks=10 : maksimal pesan per jalan}
        {--kirim : benar-benar kirim (tanpa ini hanya dry-run)}';

    protected $description = 'Kirim notifikasi WA untuk presensi yang belum diberitahukan';

    private const LABEL = [
        'Alpa'      => 'Alpa (tidak hadir tanpa keterangan)',
        'Terlambat' => 'Terlambat',
        'Sakit'     => 'Sakit',
        'Izin'      => 'Izin',
    ];

    public function handle(WaPengirim $pengirim): int
    {
        $hari   = (int) ($this->option('hari') ?: config('wa.jendela_hari'));
        $status = array_values(array_filter(array_map('trim', explode(',', $this->option('status')))));
        $maks   = max(1, (int) $this->option('maks'));
        $kirim  = (bool) $this->option('kirim');

        $q = DB::table('presensi as p')
            ->join('siswa as s', 's.id_siswa', '=', 'p.id_siswa')
            ->whereIn('p.status', $status)
            ->where('p.tanggal', '>=', now()->subDays($hari)->toDateString())
            ->where('p.tanggal', '<=', now()->toDateString())
            ->orderByDesc('p.tanggal')
            ->limit(200)
            ->select('p.id_presensi', 'p.id_siswa', 'p.status', 'p.tanggal', 's.nama as nama_siswa');

        if ($this->option('siswa')) {
            $q->where('p.id_siswa', (int) $this->option('siswa'));
        }

        $rows = $q->get();

        $sudah = DB::table('wa_log')
            ->whereIn('kunci', $rows->map(fn ($r) => 'presensi-' . $r->id_presensi)->all())
            ->pluck('kunci')
            ->flip();

        $rows = $rows->reject(fn ($r) => $sudah->has('presensi-' . $r->id_presensi));

        $this->line("Kandidat belum diberitahu: {$rows->count()} (jendela {$hari} hari)");

        $n = 0;
        foreach ($rows as $r) {
            if ($n >= $maks) {
                $this->warn("Berhenti di batas --maks={$maks}.");
                break;
            }

            $p = WaPenerima::untukSiswa((int) $r->id_siswa);
            if ($p === null) {
                $this->line("- presensi {$r->id_presensi} ({$r->nama_siswa}): tidak ada penerima layak, dilewati");
                continue;
            }

            $tanggal = Carbon::parse($r->tanggal)->locale('id')->translatedFormat('l, d F Y');
            $info = "presensi {$r->id_presensi} {$r->status} {$r->tanggal} -> "
                  . "{$p['hubungan']} " . WaPenerima::samarkan($p['nomor']);

            if (!$kirim) {
                $this->line("- DRY-RUN: {$info}");
                $n++;
                continue;
            }

            $hasil = $pengirim->kirim((int) $r->id_siswa, 'presensi', [
                'nama_ortu'  => $p['nama'],
                'nama_siswa' => $r->nama_siswa,
                'status'     => self::LABEL[$r->status] ?? $r->status,
                'tanggal'    => $tanggal,
            ], 'presensi-' . $r->id_presensi);

            $this->line("- {$info}: {$hasil['hasil']}" . (isset($hasil['error']) ? " ({$hasil['error']})" : ''));
            $n++;
        }

        return self::SUCCESS;
    }
}