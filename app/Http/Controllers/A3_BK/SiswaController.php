<?php

namespace App\Http\Controllers\A3_BK;

use App\Http\Controllers\Controller;
use App\Models\DimensiRiasec;
use App\Models\GuruBk;
use App\Models\PengajuanBk;
use App\Models\RiwayatKelas;
use App\Models\Semester;
use App\Models\SesiTes;
use App\Services\RekomendasiService;
use App\Services\TesRiasecService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/**
 * SiswaController — menangani semua halaman area siswa A3.
 */
class SiswaController extends Controller
{
    public function __construct(
        private TesRiasecService $tesService,
        private RekomendasiService $rekomendasiService,
    ) {}

    /**
     * Ambil konteks siswa aktif: riwayat kelas + semester aktif + tahun ajaran.
     */
    private function getKonteksSiswa()
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

    /**
     * Dashboard Siswa — berbeda tergantung tingkat kelas (X, XI, XII).
     */
    public function dashboard()
    {
        $ctx = $this->getKonteksSiswa();
        $tingkat = $ctx['riwayat']->kelas->tingkat;

        $sesiTes = $ctx['riwayat']->sesiTes;
        $periodeAktif = $this->tesService->periodeAktifAda($ctx['riwayat']);

        // Rekomendasi jika sudah tes
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

        // Pengajuan BK terakhir
        $pengajuanTerakhir = $ctx['riwayat']->pengajuanBk()
            ->latest()
            ->first();

        return view('bk.dashboard_siswa', array_merge($ctx, compact(
            'tingkat',
            'sesiTes',
            'periodeAktif',
            'rekomendasiMapel',
            'rekomendasiProdi',
            'pengajuanTerakhir',
        )));
    }

    /**
     * Halaman petunjuk tes RIASEC.
     */
    public function petunjukTes()
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

    /**
     * Halaman soal tes RIASEC.
     */
    public function soalTes()
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

        // Bersihkan session jawaban lama
        Session::forget('jawaban_tes');

        return view('bk.siswa.tes-soal', array_merge($ctx, compact('soal')));
    }

    /**
     * Submit jawaban tes RIASEC.
     */
    public function submitTes(Request $request)
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

        // Validasi semua soal aktif terjawab
        $soalIds = $this->tesService->getSoalAktif()->pluck('id_soal_riasec')->toArray();
        foreach ($soalIds as $idSoal) {
            if (! isset($jawaban[$idSoal])) {
                return back()->withErrors(['jawaban' => 'Semua soal harus dijawab.'])->withInput();
            }
        }

        try {
            $skor = $this->tesService->hitungSkor($jawaban);
            $sesiTes = $this->tesService->simpanHasil($ctx['riwayat'], $skor);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('siswa.hasil')
            ->with('success', 'Tes selesai! Berikut hasil pemetaan minat RIASEC kamu.');
    }

    /**
     * Halaman hasil tes + rekomendasi.
     */
    public function hasilTes()
    {
        $ctx = $this->getKonteksSiswa();
        $tingkat = $ctx['riwayat']->kelas->tingkat;

        $sesiTes = $ctx['riwayat']->sesiTes;

        if (! $sesiTes) {
            return redirect()->route('siswa.tes.petunjuk')
                ->with('info', 'Kamu belum menyelesaikan tes RIASEC pada semester ini.');
        }

        // Ambil dimensi RIASEC dengan deskripsi
        $dimensi = DimensiRiasec::all()->keyBy('kode');

        // Rekomendasi sesuai tingkat
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

    /**
     * Halaman Mata Pelajaran Pilihan — khusus kelas XI.
     */
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

        // Untuk setiap mapel, ambil prodi relevan
        $prodiRelevan = [];
        foreach ($kecocokanMapel as $km) {
            $prodiRelevan[$km->id_mapel] = $this->rekomendasiService->prodiRelevanMapel($km->id_mapel);
        }

        // Hasil tes kelas X (riwayat_kelas tingkat X siswa ini)
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

    /**
     * Riwayat semua tes siswa.
     */
    public function riwayatTes()
    {
        $ctx = $this->getKonteksSiswa();
        $riwayat = $this->rekomendasiService->riwayatTes($ctx['siswa']->id_siswa);

        return view('bk.siswa.riwayat-tes', array_merge($ctx, compact('riwayat')));
    }

    /**
     * Halaman pengajuan BK — daftar dan form baru.
     */
    public function pengajuanBk()
    {
        $ctx = $this->getKonteksSiswa();

        $pengajuan = $ctx['riwayat']->pengajuanBk()
            ->with('guruBk.guru')
            ->latest()
            ->get();

        // Kartu Guru BK (yang melayani tingkat siswa atau semua tingkat)
        $tingkat = $ctx['riwayat']->kelas->tingkat;
        $guruBkList = GuruBk::with('guru')
            ->where('aktif', true)
            ->where(function ($q) use ($tingkat) {
                $q->where('tingkat', $tingkat)->orWhereNull('tingkat');
            })
            ->get();

        $topikList = PengajuanBk::TOPIK_LIST;

        return view('bk.siswa.pengajuan-bk', array_merge($ctx, compact(
            'pengajuan',
            'guruBkList',
            'topikList',
        )));
    }

    /**
     * Simpan pengajuan BK baru.
     */
    public function storePengajuanBk(Request $request)
    {
        $ctx = $this->getKonteksSiswa();

        $request->validate([
            'topik' => 'required|string|in:'.implode(',', PengajuanBk::TOPIK_LIST),
            'deskripsi' => 'required|string|min:20|max:500',
        ]);

        PengajuanBk::create([
            'id_riwayat_kelas' => $ctx['riwayat']->id_riwayat_kelas,
            'topik' => $request->topik,
            'deskripsi' => $request->deskripsi,
            'status' => 'menunggu',
        ]);

        return redirect()->route('siswa.pengajuan-bk')
            ->with('success', 'Pengajuan bimbingan konseling berhasil dikirim.');
    }
}
