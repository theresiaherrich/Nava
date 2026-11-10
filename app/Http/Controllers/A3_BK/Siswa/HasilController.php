<?php

namespace App\Http\Controllers\A3_BK\Siswa;

use App\Http\Controllers\Controller;
use App\Models\DimensiRiasec;
use App\Models\SesiTes;
use App\Services\RekomendasiService;
use Illuminate\Support\Facades\Auth;

/**
 * HasilController — hasil tes RIASEC dan rekomendasi mapel/prodi.
 */
class HasilController extends Controller
{
    public function __construct(private RekomendasiService $rekomendasiService) {}

    public function index()
    {
        $ctx = $this->getKonteksSiswa();
        $tingkat = $ctx['riwayat']->kelas->tingkat;

        $sesiTes = $ctx['riwayat']->sesiTes;

        if (! $sesiTes) {
            return redirect()->route('siswa.tes.petunjuk')
                ->with('info', 'Kamu belum menyelesaikan tes RIASEC pada semester ini.');
        }

        $dimensi = DimensiRiasec::all()->keyBy('kode');

        $rekomendasiMapel = null;
        $rekomendasiProdi = null;

        if (in_array($tingkat, ['X', 'XI'])) {
            $rekomendasiMapel = $this->rekomendasiService->rekomendasiMapel($sesiTes->id_sesi_tes);
        }
        if ($tingkat === 'XII') {
            $rekomendasiProdi = $this->rekomendasiService->rekomendasiProdi($sesiTes->id_sesi_tes);
        }

        return view('bk.siswa.hasil', array_merge($ctx, compact(
            'tingkat',
            'sesiTes',
            'dimensi',
            'rekomendasiMapel',
            'rekomendasiProdi',
        )));
    }

    public function mapelPilihan()
    {
        $ctx = $this->getKonteksSiswa();

        if ($ctx['riwayat']->kelas->tingkat !== 'XI') {
            abort(403, 'Halaman ini hanya untuk siswa kelas XI.');
        }

        $sesiTes = $ctx['riwayat']->sesiTes;

        if (! $sesiTes) {
            return redirect()->route('siswa.hasil')
                ->with('info', 'Selesaikan tes RIASEC terlebih dahulu.');
        }

        $kecocokanMapel = $this->rekomendasiService->kecocokanMapel($sesiTes->id_sesi_tes);

        $prodiRelevan = [];
        foreach ($kecocokanMapel as $km) {
            $prodiRelevan[$km->id_mapel] = $this->rekomendasiService->prodiRelevanMapel($km->id_mapel);
        }

        $sesiTesX = SesiTes::query()
            ->join('riwayat_kelas as rk', 'rk.id_riwayat_kelas', '=', 'sesi_tes.id_riwayat_kelas')
            ->join('kelas as k', 'k.id_kelas', '=', 'rk.id_kelas')
            ->where('rk.id_siswa', $ctx['siswa']->id_siswa)
            ->where('k.tingkat', 'X')
            ->select('sesi_tes.*')
            ->first();

        return view('bk.siswa.mapel-pilihan', array_merge($ctx, compact(
            'sesiTes',
            'sesiTesX',
            'kecocokanMapel',
            'prodiRelevan',
        )));
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
