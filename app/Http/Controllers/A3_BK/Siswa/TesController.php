<?php

namespace App\Http\Controllers\A3_BK\Siswa;

use App\Http\Controllers\Controller;
use App\Services\TesRiasecService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/**
 * TesController — alur tes RIASEC siswa (petunjuk, soal, submit).
 */
class TesController extends Controller
{
    public function __construct(private TesRiasecService $tesService) {}

    public function petunjuk()
    {
        $ctx = $this->getKonteksSiswa();

        if (! $this->tesService->periodeAktifAda($ctx['riwayat'])) {
            return view('bk.siswa.tes-tidak-aktif', $ctx);
        }

        if ($this->tesService->sudahTes($ctx['riwayat'])) {
            return redirect()->route('siswa.hasil')
                ->with('info', 'Kamu sudah menyelesaikan tes RIASEC pada semester ini.');
        }

        $jumlahSoal = $this->tesService->getSoalAktif()->count();

        return view('bk.siswa.tes-petunjuk', array_merge($ctx, compact('jumlahSoal')));
    }

    public function soal()
    {
        $ctx = $this->getKonteksSiswa();

        if (! $this->tesService->periodeAktifAda($ctx['riwayat'])) {
            return redirect()->route('siswa.tes.petunjuk');
        }

        if ($this->tesService->sudahTes($ctx['riwayat'])) {
            return redirect()->route('siswa.hasil')
                ->with('info', 'Kamu sudah menyelesaikan tes RIASEC pada semester ini.');
        }

        $soal = $this->tesService->getSoalAktif();

        Session::forget('jawaban_tes');

        return view('bk.siswa.tes-soal', array_merge($ctx, compact('soal')));
    }

    public function submit(Request $request)
    {
        $ctx = $this->getKonteksSiswa();

        if ($this->tesService->sudahTes($ctx['riwayat'])) {
            return redirect()->route('siswa.hasil')
                ->with('info', 'Kamu sudah menyelesaikan tes RIASEC pada semester ini.');
        }

        $request->validate([
            'jawaban' => 'required|array',
            'jawaban.*' => 'required|integer|between:1,5',
        ]);

        $jawaban = $request->jawaban;

        $soalIds = $this->tesService->getSoalAktif()->pluck('id_soal_riasec')->toArray();
        foreach ($soalIds as $idSoal) {
            if (! isset($jawaban[$idSoal])) {
                return back()->withErrors(['jawaban' => 'Semua soal harus dijawab.'])->withInput();
            }
        }

        try {
            $skor = $this->tesService->hitungSkor($jawaban);
            $this->tesService->simpanHasil($ctx['riwayat'], $skor);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('siswa.hasil')
            ->with('success', 'Tes selesai! Berikut hasil pemetaan minat RIASEC kamu.');
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
