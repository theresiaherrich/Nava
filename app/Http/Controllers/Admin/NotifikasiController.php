<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class NotifikasiController extends Controller
{
    public function index(Request $request)
    {
        $waConfigured = (bool) config('wa.token') && (bool) config('wa.url');
        $waAktif = (bool) config('wa.aktif');
        $statusService = $this->statusService();

        $q = DB::table('wa_log as l')
            ->leftJoin('siswa as s', 's.id_siswa', '=', 'l.id_siswa')
            ->select('l.*', 's.nama as nama_siswa')
            ->orderByDesc('l.id');

        if ($request->filled('status')) {
            $q->where('l.status', $request->input('status'));
        }
        if ($request->filled('q')) {
            $q->where('s.nama', 'like', '%' . $request->input('q') . '%');
        }

        $logs = $q->paginate(15)->withQueryString();

        $ringkas = DB::table('wa_log')->selectRaw(
            "COUNT(*) total, SUM(status='diterima') diterima, SUM(status='gagal') gagal"
        )->first();

        return view('admin.notifications.index', [
            'logs' => $logs,
            'total' => (int) $ringkas->total,
            'diterima' => (int) $ringkas->diterima,
            'failed' => (int) $ringkas->gagal,
            'waConfigured' => $waConfigured,
            'waAktif' => $waAktif,
            'statusService' => $statusService,
        ]);
    }


public function kirimSekarang()
{
    if (!config('wa.aktif')) {
        return back()->with(
            'wa_error',
            'Pengiriman WA sedang nonaktif (WA_AKTIF=false).'
        );
    }

    try {
        $exitCode = Artisan::call('wa:kirim-presensi', [
            '--kirim' => true,
            '--maks' => 10,
        ]);

        $output = trim(Artisan::output());

        if ($exitCode !== 0) {
            return back()->with(
                'wa_error',
                'Proses gagal. ' . $output
            );
        }

        if (preg_match('/Kandidat belum diberitahu:\s*0/', $output)) {
            return back()->with(
                'wa_pesan',
                'Tidak ada notifikasi baru untuk dikirim. ' . $output
            );
        }

        return back()->with(
            'wa_pesan',
            'Proses pengiriman selesai. ' . $output
        );

    } catch (\Throwable $e) {
        report($e);

        return back()->with(
            'wa_error',
            'Terjadi kesalahan saat menjalankan pengiriman WhatsApp.'
        );
    }
}


public function kirimRekap()
{
    if (!config('wa.aktif')) {
        return back()->with(
            'wa_error',
            'Pengiriman WA sedang nonaktif.'
        );
    }

    try {
        $exitCode = Artisan::call('wa:rekap-presensi', [
            '--kirim' => true,
        ]);

        $output = trim(Artisan::output());

        if ($exitCode !== 0) {
            return back()->with(
                'wa_error',
                'Rekap gagal dijalankan: ' . $output
            );
        }

        return back()->with(
            'wa_pesan',
            'Proses rekap mingguan selesai. ' . $output
        );
    } catch (\Throwable $e) {
        report($e);

        return back()->with(
            'wa_error',
            'Terjadi kesalahan saat mengirim rekap mingguan.'
        );
    }
}


    public function ulang(int $id)
    {
        $log = DB::table('wa_log')->where('id', $id)->where('status', 'gagal')->first();

        if (!$log) {
            return back()->with('wa_error', 'Data tidak ditemukan atau bukan berstatus gagal.');
        }
        if (!str_starts_with($log->kunci, 'presensi-')) {
            return back()->with('wa_error', 'Hanya notifikasi presensi yang bisa dikirim ulang.');
        }

        // Pemindai hanya mengirim presensi yang belum ada di wa_log, jadi baris gagal dihapus.
        DB::table('wa_log')->where('id', $id)->delete();

        return back()->with('wa_pesan', 'Dijadwalkan ulang. Akan terkirim di putaran berikutnya, atau klik "Kirim sekarang".');
    }

    // Status WhatsApp dari service Node; null kalau service tidak terjangkau.
    private function statusService(): ?string
    {
        if (!config('wa.token') || !config('wa.url')) {
            return null;
        }
        try {
            return Http::withHeaders(['X-WA-Token' => config('wa.token')])
                ->timeout(2)->get(rtrim(config('wa.url'), '/') . '/api/status')
                ->json('status');
        } catch (\Throwable $e) {
            return null;
        }
    }
}
