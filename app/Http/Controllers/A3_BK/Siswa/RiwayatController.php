<?php

namespace App\Http\Controllers\A3_BK\Siswa;

use App\Http\Controllers\Controller;
use App\Services\RekomendasiService;
use Illuminate\Support\Facades\Auth;

/**
 * RiwayatController — riwayat semua tes RIASEC siswa.
 */
class RiwayatController extends Controller
{
    public function __construct(private RekomendasiService $rekomendasiService) {}

    public function index()
    {
        $ctx = $this->getKonteksSiswa();
        $riwayat = $this->rekomendasiService->riwayatTes($ctx['siswa']->id_siswa);

        return view('bk.siswa.riwayat-tes', array_merge($ctx, compact('riwayat')));
    }

    private function getKonteksSiswa(): array
    {
        $user = Auth::user();
        $siswa = $user->siswa;

        if (! $siswa) {
            abort(403, 'Data siswa tidak ditemukan.');
        }

        $semester = \App\Models\Semester::where('aktif', true)->with('tahunAjaran')->first();

        if (! $semester) {
            abort(503, 'Tidak ada semester aktif saat ini.');
        }

        $riwayat = \App\Models\RiwayatKelas::with('kelas.tahunAjaran', 'semester', 'sesiTes')
            ->where('id_siswa', $siswa->id_siswa)
            ->where('id_semester', $semester->id_semester)
            ->first();

        if (! $riwayat) {
            abort(503, 'Data kelas Anda untuk semester ini belum tersedia.');
        }

        return compact('siswa', 'semester', 'riwayat');
    }
}
