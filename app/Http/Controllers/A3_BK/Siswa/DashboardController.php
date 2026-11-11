<?php

namespace App\Http\Controllers\A3_BK\Siswa;

use App\Http\Controllers\Controller;
use App\Models\RiwayatKelas;
use App\Models\Semester;

use App\Services\RekomendasiService;
use App\Services\TesRiasecService;
use Illuminate\Support\Facades\Auth;

/**
 * DashboardController â€” halaman utama area siswa.
 */
class DashboardController extends Controller
{
    public function __construct(
        private TesRiasecService $tesService,
        private RekomendasiService $rekomendasiService,
    ) {}

    public function index()
    {
        $ctx = $this->getKonteksSiswa();
        $tingkat = $ctx['riwayat']->kelas->tingkat; // 'X', 'XI', atau 'XII'

        $sesiTes = $ctx['riwayat']->sesiTes;
        $periodeAktif = $this->tesService->periodeAktifAda($ctx['riwayat']);

        // Khusus Kelas XI: jika belum ada sesi tes di semester ini, cari sesi tes sewaktu Kelas X
        if ($tingkat === 'XI' && ! $sesiTes) {
            $riwayatKelasX = RiwayatKelas::where('id_siswa', $ctx['siswa']->id_siswa)
                ->whereHas('kelas', function ($q) {
                    $q->where('tingkat', 'X');
                })
                ->whereHas('sesiTes')
                ->latest()
                ->first();

            if ($riwayatKelasX) {
                $sesiTes = $riwayatKelasX->sesiTes;
            }
        }

        $rekomendasiMapel = null;
        $rekomendasiProdi = null;

        if ($sesiTes) {
            if (in_array($tingkat, ['X', 'XI'])) {
                $rekomendasiMapel = $this->rekomendasiService->rekomendasiMapel($sesiTes->id_sesi_tes);
            }
            if ($tingkat === 'XII') {
                $rekomendasiProdi = $this->rekomendasiService->rekomendasiProdi($sesiTes->id_sesi_tes);
            }
        }

        $pengajuanTerakhir = $ctx['riwayat']->pengajuanBk()
            ->latest()
            ->first();

        return view('bk.siswa.dashboard', array_merge($ctx, compact(
            'tingkat',
            'sesiTes',
            'periodeAktif',
            'rekomendasiMapel',
            'rekomendasiProdi',
            'pengajuanTerakhir'
        )));
    }

    private function getKonteksSiswa(): array
    {
        $user = Auth::user();
        $siswa = $user->siswa;

        if (! $siswa) {
            abort(403, 'Data siswa tidak ditemukan.');
        }

        $semester = Semester::where('aktif', true)->with('tahunAjaran')->first();

        if (! $semester) {
            abort(503, 'Tidak ada semester aktif saat ini.');
        }

        $riwayat = RiwayatKelas::with('kelas.tahunAjaran', 'semester', 'sesiTes')
            ->where('id_siswa', $siswa->id_siswa)
            ->where('id_semester', $semester->id_semester)
            ->first();

        if (! $riwayat) {
            abort(503, 'Data kelas Anda untuk semester ini belum tersedia.');
        }

        return compact('siswa', 'semester', 'riwayat');
    }
}
