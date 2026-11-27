<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Support\Dummy\DashboardWaliKelasDummy;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Dashboard Kelas untuk Wali Kelas (desain wali-kelas 1.01; ?state=rapor-siap = desain 5.02). */
class DashboardWaliKelasController extends Controller
{
    public function __invoke(Request $request): View
    {
        // TODO API: ganti dengan data dari database (lihat App\Support\Dummy\DashboardWaliKelasDummy).
        $data = DashboardWaliKelasDummy::get($request->query('state') === 'rapor-siap');

        $verifikasi = url('/wali-kelas/verifikasi-nilai');
        $tautan = [
            'verifikasi' => $verifikasi,
            'cetak' => url('/wali-kelas/cetak-rapor'),
            // Halaman Pelengkap Rapor wali kelas belum ada (baru API /api/wali/pelengkap): null = tombol nonaktif.
            'pelengkap' => null,
        ];
        $linkMapel = fn (?int $id) => $id ? $verifikasi.'?mapel='.$id : $verifikasi;

        $data['mapel'] = array_map(fn (array $m) => [...$m, 'url' => $linkMapel($m['id'])], $data['mapel']);
        $data['perlu_tindakan'] = array_map(fn (array $t) => [...$t, 'url' => match ($t['jenis']) {
            'verifikasi' => $linkMapel($t['id_mapel']),
            'pelengkap' => $tautan['pelengkap'],
            default => null,
        }], $data['perlu_tindakan']);

        return view('wali-kelas.dashboard', [...$data, 'tautan' => $tautan]);
    }
}
