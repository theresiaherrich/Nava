<?php

namespace App\Http\Controllers\A3_BK\Siswa;

use App\Http\Controllers\Controller;
use App\Services\TesRiasecService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

/**
 * TesController — alur tes RIASEC siswa (petunjuk, soal, submit).
 */
class TesController extends Controller
{
    public function __construct(private TesRiasecService $tesService, private \App\Services\RekomendasiService $rekomService) {}

    public function petunjuk()
    {
        $ctx = $this->getKonteksSiswa();

        // Validasi: Hanya Kelas X dan XII yang boleh ikut tes
        if (! in_array($ctx['riwayat']->kelas->tingkat, ['X', 'XII'])) {
            return redirect()->route('siswa.dashboard')
                ->with('error', 'Siswa Kelas XI tidak dapat mengikuti tes RIASEC baru.');
        }

        if (! $this->tesService->periodeAktifAda($ctx['riwayat'])) {
            return view('bk.siswa.tes-tidak-aktif', $ctx);
        }

        // Cegah pengambilan tes kembali untuk kelas X atau XII (hanya satu kali)
        if (in_array($ctx['riwayat']->kelas->tingkat, ['X', 'XII']) && $this->tesService->sudahTes($ctx['riwayat'])) {
            return redirect()->route('siswa.hasil')
                ->with('error', 'Kamu hanya dapat melakukan tes sekali per semester.');
        }

        $jumlahSoal = $this->tesService->getSoalAktif()->count();

        return view('bk.siswa.tes-petunjuk', array_merge($ctx, compact('jumlahSoal')));
    }

    public function soal()
    {
        $ctx = $this->getKonteksSiswa();

        // Validasi: Hanya Kelas X dan XII yang boleh ikut tes
        if (! in_array($ctx['riwayat']->kelas->tingkat, ['X', 'XII'])) {
            return redirect()->route('siswa.dashboard')
                ->with('error', 'Siswa Kelas XI tidak dapat mengikuti tes RIASEC baru.');
        }

        if (! $this->tesService->periodeAktifAda($ctx['riwayat'])) {
            return redirect()->route('siswa.tes.petunjuk');
        }

        // Cegah pengambilan tes kembali untuk kelas X atau XII (hanya satu kali)
        if (in_array($ctx['riwayat']->kelas->tingkat, ['X', 'XII']) && $this->tesService->sudahTes($ctx['riwayat'])) {
            return redirect()->route('siswa.hasil')
                ->with('error', 'Kamu hanya dapat melakukan tes sekali per semester.');
        }

        $soal = $this->tesService->getSoalAktif();

        Session::forget('jawaban_tes');

        return view('bk.siswa.tes-soal', array_merge($ctx, compact('soal')));
    }

    public function submit(Request $request)
    {
        $ctx = $this->getKonteksSiswa();

        // Validasi: Hanya Kelas X dan XII yang boleh ikut tes
        if (! in_array($ctx['riwayat']->kelas->tingkat, ['X', 'XII'])) {
            return redirect()->route('siswa.dashboard')
                ->with('error', 'Siswa Kelas XI tidak dapat mengikuti tes RIASEC baru.');
        }

        // Cegah pengambilan tes kembali untuk kelas X atau XII (hanya satu kali)
        if (in_array($ctx['riwayat']->kelas->tingkat, ['X', 'XII']) && $this->tesService->sudahTes($ctx['riwayat'])) {
            return redirect()->route('siswa.hasil')
                ->with('error', 'Kamu hanya dapat melakukan tes sekali per semester.');
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
            $sesi = $this->tesService->simpanHasil($ctx['riwayat'], $skor);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        $tingkat = $ctx['riwayat']->kelas->tingkat;

        // ── Kelas X → simpan 5 rekomendasi mapel ───────────────────────────
        if ($tingkat === 'X') {
            $rekomMapel = $this->rekomService->rekomendasiMapel($sesi->id_sesi_tes);
            foreach ($rekomMapel as $item) {
                DB::table('hasil_rekomendasi_kelas_10')->insert([
                    'id_sesi_tes' => $sesi->id_sesi_tes,
                    'id_mapel'    => $item->id_mapel,
                    'ranking'     => $item->peringkat,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        }

        // ── Kelas XII → simpan rekomendasi prodi + profesi (karier) ─────────
        if ($tingkat === 'XII') {
            $rekomProdi = $this->rekomService->rekomendasiProdi($sesi->id_sesi_tes);
            foreach ($rekomProdi as $item) {
                $profesi = $this->rekomService->profesiProdi($item->id_prodi)->first();
                DB::table('hasil_rekomendasi_kelas_12')->insert([
                    'id_sesi_tes' => $sesi->id_sesi_tes,
                    'id_prodi'    => $item->id_prodi,
                    'id_profesi'  => $profesi->id_profesi ?? null,
                    'peringkat'   => $item->peringkat,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
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