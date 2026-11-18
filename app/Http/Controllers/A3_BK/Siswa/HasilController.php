<?php

namespace App\Http\Controllers\A3_BK\Siswa;

use App\Http\Controllers\Controller;
use App\Models\DimensiRiasec;
use App\Models\RiwayatKelas;
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

        $sesiTesQuery = SesiTes::with(['hasilTes.dimensi'])
            ->where('status', 'selesai');

        if ($tingkat === 'XI') {
            $riwayatKelasX = RiwayatKelas::where('id_siswa', $ctx['siswa']->id_siswa)
                ->whereHas('kelas', fn ($q) => $q->where('tingkat', 'X'))
                ->pluck('id_riwayat_kelas');

            $sesiTesQuery->whereIn('id_riwayat_kelas', $riwayatKelasX);
        } else {
            $sesiTesQuery->where('id_riwayat_kelas', $ctx['riwayat']->id_riwayat_kelas);
        }
        $sesiTes = $sesiTesQuery->orderByDesc('waktu_selesai')->first();

        if (! $sesiTes) {
            if ($tingkat === 'XI') {
                return redirect()->route('siswa.dashboard')
                    ->with('info', 'Data tes RIASEC kelas X belum tersedia.');
            }

            return redirect()->route('siswa.tes.petunjuk')
                ->with('info', 'Kamu belum memiliki hasil tes RIASEC untuk jenjang ini. Silakan ikuti tes terlebih dahulu.');
        }

        $dimensi = DimensiRiasec::all()->keyBy('kode');

        $rekomendasiMapel = null;
        $rekomendasiProdi = null;

        if (in_array($tingkat, ['X', 'XI'])) {
            $rekomendasiMapel = $this->rekomendasiService->rekomendasiMapel($sesiTes->id_sesi_tes)->take(5);
        }
        if ($tingkat === 'XII') {
            $rekomendasiProdi = $this->rekomendasiService->rekomendasiProdi($sesiTes->id_sesi_tes)->take(5);
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
        $tingkat = $ctx['riwayat']->kelas->tingkat;

        if (! in_array($tingkat, ['X', 'XI'], true)) {
            abort(403, 'Rekomendasi mata pelajaran tidak tersedia untuk jenjang Anda.');
        }

        if ($tingkat === 'X') {
            // Ambil sesi tes kelas X semester aktif
            $sesiTesX = SesiTes::with(['hasilTes.dimensi'])
                ->where('id_riwayat_kelas', $ctx['riwayat']->id_riwayat_kelas)
                ->where('status', 'selesai')
                ->orderByDesc('waktu_selesai')
                ->first();

            if (! $sesiTesX) {
                return redirect()->route('siswa.tes.petunjuk')
                    ->with('info', 'Kamu harus menyelesaikan tes RIASEC terlebih dahulu untuk melihat rekomendasi mata pelajaran pilihan.');
            }
        } else {
            $riwayatKelasX = RiwayatKelas::where('id_siswa', $ctx['siswa']->id_siswa)
                ->whereHas('kelas', fn ($q) => $q->where('tingkat', 'X'))
                ->pluck('id_riwayat_kelas');

            $sesiTesX = SesiTes::with(['hasilTes.dimensi'])
                ->whereIn('id_riwayat_kelas', $riwayatKelasX)
                ->where('status', 'selesai')
                ->orderByDesc('waktu_selesai')
                ->first();

            if (! $sesiTesX) {
                return redirect()->route('siswa.dashboard')
                    ->with('info', 'Data riwayat tes RIASEC kelas X tidak ditemukan.');
            }
        }

        $rekomendasiMapel = $this->rekomendasiService->rekomendasiMapel($sesiTesX->id_sesi_tes)->take(5);

        return view('bk.siswa.mapel-pilihan', array_merge($ctx, compact(
            'tingkat',
            'sesiTesX',
            'rekomendasiMapel'
        )));
    }

    public function rekomendasiProdi()
    {
        $ctx = $this->getKonteksSiswa();
        $tingkat = $ctx['riwayat']->kelas->tingkat;

        $sesiTes = SesiTes::with(['hasilTes.dimensi'])
            ->where('id_riwayat_kelas', $ctx['riwayat']->id_riwayat_kelas)
            ->where('status', 'selesai')
            ->orderByDesc('waktu_selesai')
            ->first();

        if (! $sesiTes) {
            $riwayatLain = RiwayatKelas::where('id_siswa', $ctx['siswa']->id_siswa)
                ->pluck('id_riwayat_kelas');

            $sesiTes = SesiTes::with(['hasilTes.dimensi'])
                ->whereIn('id_riwayat_kelas', $riwayatLain)
                ->where('status', 'selesai')
                ->orderByDesc('waktu_selesai')
                ->first();
        }

        if (! $sesiTes) {
            return redirect()->route('siswa.dashboard')
                ->with('info', 'Kamu harus menyelesaikan tes RIASEC terlebih dahulu untuk melihat rekomendasi program studi.');
        }

        $rekomendasiProdi = $this->rekomendasiService->rekomendasiProdi($sesiTes->id_sesi_tes)->take(5);

        return view('bk.siswa.rekomendasi-prodi', array_merge($ctx, compact(
            'tingkat',
            'sesiTes',
            'rekomendasiProdi'
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

        $riwayat = \App\Models\RiwayatKelas::with('kelas.tahunAjaran', 'semester')
            ->where('id_siswa', $siswa->id_siswa)
            ->where('id_semester', $semester->id_semester)
            ->first();

        if (! $riwayat) {
            abort(503, 'Data kelas Anda untuk semester ini belum tersedia.');
        }

        return compact('siswa', 'semester', 'riwayat');
    }
}
