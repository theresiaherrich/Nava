<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\DB;

class WaPengirim
{
    public function __construct(private WaNotif $wa) {}

    /**
     * Kirim satu notifikasi ke penerima layak milik siswa.
     * Hasil: ['hasil' => 'diterima'|'tidak_ada_penerima'|'duplikat'|'gagal', 'error'? => ...]
     */
    public function kirim(int $idSiswa, string $template, array $data, string $kunci): array
    {
        $p = WaPenerima::untukSiswa($idSiswa);
        if ($p === null) {
            return ['hasil' => 'tidak_ada_penerima'];
        }

        $sudah = DB::table('wa_log')->where('kunci', $kunci)->exists();
        if ($sudah) {
            return ['hasil' => 'duplikat'];
        }

        $now = now();
        DB::table('wa_log')->insert([
            'id_siswa'    => $idSiswa,
            'hubungan'    => $p['hubungan'],
            'nomor_samar' => WaPenerima::samarkan($p['nomor']),
            'template'    => $template,
            'kunci'       => $kunci,
            'status'      => 'diproses',
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);

        try {
            $this->wa->send($p['nomor'], $template, $data, $kunci);
            $this->tandai($kunci, 'diterima');
            return ['hasil' => 'diterima'];
        } catch (RequestException $e) {
             $err = $e->response?->json('error') ?? 'HTTP_' . $e->response?->status();

        // Node menjawab DUPLIKAT = pesan sudah pernah diterima service
         if ($err === 'DUPLIKAT') {
            $this->tandai($kunci, 'diterima');
            return ['hasil' => 'diterima'];
        }

         $this->tandai($kunci, 'gagal', $err);
         return ['hasil' => 'gagal', 'error' => $err];
}
    }

    private function tandai(string $kunci, string $status, ?string $error = null): void
    {
        DB::table('wa_log')->where('kunci', $kunci)->update([
            'status'     => $status,
            'error'      => $error,
            'updated_at' => now(),
        ]);
    }
}