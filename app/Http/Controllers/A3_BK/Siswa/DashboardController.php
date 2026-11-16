<?php

namespace App\Http\Controllers\A3_BK\Siswa;

use App\Http\Controllers\Controller;
use App\Models\PengajuanBk;
use App\Models\RiwayatKelas;
use App\Models\DimensiRiasec;
use App\Models\Semester;
use App\Models\SesiTes;
use App\Services\RekomendasiService;
use App\Services\TesRiasecService;
use Illuminate\Support\Facades\Auth;

/**
 * DashboardController — halaman utama area siswa.
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

        $sesiTesQuery = SesiTes::with('hasilTes.dimensi')
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

        $skorPerDimensi = $sesiTes?->hasilTes->keyBy(fn ($hasil) => $hasil->dimensi?->kode);
        $profilRiasec = DimensiRiasec::query()
            ->orderBy('id_dimensi')
            ->get(['kode', 'nama'])
            ->map(fn (DimensiRiasec $dimensi): array => [
                'kode' => $dimensi->kode,
                'nama' => $dimensi->nama,
                'nilai' => (float) ($skorPerDimensi?->get($dimensi->kode)?->skor_normal ?? 0),
            ]);

        $rekomendasiMapel = null;
        $rekomendasiProdi = null;

        if ($sesiTes) {
            if (in_array($tingkat, ['X', 'XI'])) {
                $rekomendasiMapel = $this->rekomendasiService->rekomendasiMapel($sesiTes->id_sesi_tes)->take(5);
            }
            if ($tingkat === 'XII') {
                $rekomendasiProdi = $this->rekomendasiService->rekomendasiProdi($sesiTes->id_sesi_tes)->take(5);
            }
        }

        $pengajuanTerakhir = PengajuanBk::where('id_siswa', $ctx['siswa']->id_siswa)
            ->where('id_semester', $ctx['semester']->id_semester)
            ->latest('created_at')
            ->first();
        $aktivitas = collect();
        if ($sesiTes?->waktu_selesai) {
            $aktivitas->push([
                'judul' => 'Tes RIASEC selesai',
                'detail' => 'Profil minat dan rekomendasi diperbarui',
                'waktu' => $sesiTes->waktu_selesai,
            ]);
        }
        if ($pengajuanTerakhir) {
            $aktivitas->push([
                'judul' => 'Pengajuan BK '.str_replace('_', ' ', $pengajuanTerakhir->status),
                'detail' => $pengajuanTerakhir->topik,
                'waktu' => $pengajuanTerakhir->updated_at ?? $pengajuanTerakhir->created_at,
            ]);
        }
        $aktivitas = $aktivitas->sortByDesc('waktu')->values();
        $greeting = match (now()->hour) {
            0, 1, 2, 3, 4 => 'malam',
            5, 6, 7, 8, 9, 10 => 'pagi',
            11, 12, 13, 14 => 'siang',
            15, 16, 17 => 'sore',
            default => 'malam',
        };

        return view('bk.siswa.dashboard', array_merge($ctx, compact(
            'tingkat',
            'sesiTes',
            'profilRiasec',
            'rekomendasiMapel',
            'rekomendasiProdi',
            'pengajuanTerakhir',
            'aktivitas',
            'greeting',
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

        $riwayat = RiwayatKelas::with('kelas.tahunAjaran', 'semester')
            ->where('id_siswa', $siswa->id_siswa)
            ->where('id_semester', $semester->id_semester)
            ->first();

        if (! $riwayat) {
            abort(503, 'Data kelas Anda untuk semester ini belum tersedia.');
        }

        return compact('siswa', 'semester', 'riwayat');
    }
}
