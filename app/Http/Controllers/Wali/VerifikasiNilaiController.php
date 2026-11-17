<?php

namespace App\Http\Controllers\Wali;

use App\Http\Controllers\Controller;
use App\Support\Dummy\Wali;
use Illuminate\Http\Request;

class VerifikasiNilaiController extends Controller
{
    /**
     * Verifikasi Nilai wali kelas (desain Wali 2.01–2.09).
     * Varian: ?state=kosong|terkunci, ?paket=p-4 (disetujui + riwayat), ?paket=p-6 (rombel campuran),
     *         ?modal=revisi|revisi-error|setujui, ?pilih-periode=1.
     */
    public function index(Request $request)
    {
        $state = in_array($request->query('state'), ['kosong', 'terkunci'], true) ? $request->query('state') : 'normal';
        $paket = Wali::paket($state);

        // Contoh 2.07: Kimia sudah disetujui sehingga riwayat lengkap tampil.
        if ($request->query('paket') === 'p-4' && $state === 'normal') {
            $paket[3]['status'] = 'disetujui';
            array_unshift($paket[3]['riwayat'], ['jenis' => 'disetujui', 'judul' => 'Disetujui', 'oleh' => 'Siti Rahmawati (Wali Kelas)', 'waktu' => '14 Okt 2026, 09.10']);
        }

        return view('wali.verifikasi-nilai', [
            'kelas' => Wali::KELAS,
            'paket' => $paket,
            'aktif' => $request->query('paket', 'p-1'),
            'state' => $state,
        ]);
    }
}
