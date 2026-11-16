<?php

namespace App\Http\Controllers\A3_BK\Siswa;

use App\Http\Controllers\Controller;
use App\Services\RekomendasiService;
use App\Services\TesRiasecService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/**
 * TesController — alur tes RIASEC siswa (petunjuk, soal, submit).
 */
class TesController extends Controller
{
    public function __construct(
        private TesRiasecService $tesService,
        private RekomendasiService $rekomService
    ) {}

    public function petunjuk()
    {
        $ctx = $this->getKonteksSiswa();
        $tingkat = $ctx['riwayat']->kelas->tingkat;

        if (! in_array($tingkat, ['X', 'XII'])) {
            abort(403, 'Jenjang Anda tidak memiliki akses untuk mengerjakan tes RIASEC.');
        }

        $jumlahSoal = $this->tesService->getSoalAktif()->count();

        return view('bk.siswa.tes-petunjuk', array_merge($ctx, compact('jumlahSoal', 'tingkat')));
    }

    public function soal()
    {
        $ctx = $this->getKonteksSiswa();
        $tingkat = $ctx['riwayat']->kelas->tingkat;

        if (! in_array($tingkat, ['X', 'XII'])) {
            abort(403, 'Jenjang Anda tidak memiliki akses untuk mengerjakan tes RIASEC.');
        }

        $soal = $this->tesService->getSoalAktif();

        Session::forget('jawaban_tes');

        return view('bk.siswa.tes-soal', array_merge($ctx, compact('soal', 'tingkat')));
    }

    public function submit(Request $request)
    {
        $ctx = $this->getKonteksSiswa();
        $tingkat = $ctx['riwayat']->kelas->tingkat;

        if (! in_array($tingkat, ['X', 'XII'])) {
            abort(403, 'Jenjang Anda tidak memiliki akses untuk mengerjakan tes RIASEC.');
        }

        $request->validate([
            'jawaban' => 'required|array',
            'jawaban.*' => 'required|integer|between:1,5',
        ]);

        $jawaban = $request->jawaban;

        $soalAktif = $this->tesService->getSoalAktif();
        foreach ($soalAktif as $item) {
            if (! isset($jawaban[$item->id_soal_riasec])) {
                return back()->withErrors(['jawaban' => 'Semua soal harus dijawab.'])->withInput();
            }
        }

        try {
            // 1. Hitung skor per dimensi
            $skor = $this->tesService->hitungSkor($jawaban);

            // 2. Simpan sesi tes, jawaban, dan hasil skor per dimensi
            $sesi = $this->tesService->simpanHasil($ctx['riwayat'], $jawaban, $skor, $tingkat);

            // 3. Hitung dan simpan rekomendasi
            if ($tingkat === 'X') {
                $this->rekomService->hitungDanSimpanMapel($sesi);
            } elseif ($tingkat === 'XII') {
                $this->rekomService->hitungDanSimpanProdi($sesi);
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat menyimpan tes: ' . $e->getMessage());
        }

        return redirect()->route('siswa.hasil')
            ->with('success', 'Tes RIASEC selesai! Berikut hasil pemetaan minat dan rekomendasi kamu.');
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
