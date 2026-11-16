<?php

namespace App\Http\Controllers\A3_BK;

use App\Http\Controllers\Controller;
use App\Models\DimensiRiasec;
use App\Models\Guru;
use App\Models\Mapel;
use App\Models\PengajuanBk;
use App\Models\ProgramStudi;
use App\Models\RiwayatKelas;
use App\Models\Semester;
use App\Models\SesiTes;
use App\Services\RekomendasiService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * GuruBkController — semua halaman area Guru BK.
 */
class GuruBkController extends Controller
{
    public function __construct(private RekomendasiService $rekomService) {}

    /**
     * Ambil konteks Guru BK yang sedang login.
     */
    private function getKonteksGuru(): array
    {
        $user = Auth::user();
        $guru = Guru::where('id_user', $user->id_user)->firstOrFail();

        $semester = Semester::where('aktif', true)
            ->with('tahunAjaran')
            ->orderByDesc('id_semester')
            ->first();

        if (! $semester) {
            abort(503, 'Tidak ada semester aktif saat ini.');
        }

        $penugasan = $guru->penugasanBkAktif($semester->id_semester);

        return compact('user', 'guru', 'semester', 'penugasan');
    }

    /**
     * Dashboard Guru BK — statistik real tanpa view.
     */
    public function dashboard()
    {
        $ctx = $this->getKonteksGuru();
        $idSemester = $ctx['semester']->id_semester;
        $idGuru = $ctx['guru']->id_guru;
        $tingkatDitugaskan = $ctx['penugasan']->pluck('tingkat')->unique()->values()->all();

        $riwayatSiswaQuery = RiwayatKelas::query()
            ->where('id_semester', $idSemester)
            ->whereHas('kelas', fn ($query) => $query->whereIn('tingkat', $tingkatDitugaskan));
        $totalSiswa = (clone $riwayatSiswaQuery)->count();

        $sesiSelesaiQuery = DB::table('sesi_tes')
            ->join('riwayat_kelas as riwayat', 'riwayat.id_riwayat_kelas', '=', 'sesi_tes.id_riwayat_kelas')
            ->join('kelas', 'kelas.id_kelas', '=', 'riwayat.id_kelas')
            ->where('riwayat.id_semester', $idSemester)
            ->where('sesi_tes.status', 'selesai')
            ->whereIn('kelas.tingkat', $tingkatDitugaskan);

        $sudahTes = (clone $sesiSelesaiQuery)->distinct()->count('riwayat.id_siswa');
        $rekapStats = (object) [
            'total_siswa' => $totalSiswa,
            'sudah_tes' => $sudahTes,
            'belum_tes' => max(0, $totalSiswa - $sudahTes),
        ];

        $pengajuanQuery = PengajuanBk::query()
            ->where('id_semester', $idSemester)
            ->where('id_guru', $idGuru);
        $pengajuanStats = [
            'menunggu' => (clone $pengajuanQuery)->where('status', 'menunggu')->count(),
            'diproses' => (clone $pengajuanQuery)->where('status', 'diproses')->count(),
            'dijadwalkan' => (clone $pengajuanQuery)->where('status', 'dijadwalkan')->count(),
            'selesai' => (clone $pengajuanQuery)->where('status', 'selesai')->count(),
        ];
        $menungguValidasi = Mapel::where('status_validasi', 'menunggu')->where('is_pilihan', true)->count()
            + ProgramStudi::where('status_validasi', 'menunggu')->count();

        $skorRataRata = DB::table('hasil_tes_riasec as hasil')
            ->join('dimensi_riasec as dimensi', 'dimensi.id_dimensi', '=', 'hasil.id_dimensi')
            ->join('sesi_tes as sesi', 'sesi.id_sesi_tes', '=', 'hasil.id_sesi_tes')
            ->join('riwayat_kelas as riwayat', 'riwayat.id_riwayat_kelas', '=', 'sesi.id_riwayat_kelas')
            ->join('kelas', 'kelas.id_kelas', '=', 'riwayat.id_kelas')
            ->where('riwayat.id_semester', $idSemester)
            ->where('sesi.status', 'selesai')
            ->whereIn('kelas.tingkat', $tingkatDitugaskan)
            ->groupBy('dimensi.kode')
            ->selectRaw('dimensi.kode as kode, ROUND(AVG(hasil.skor_normal), 1) as rata_rata')
            ->pluck('rata_rata', 'kode');
        $distribusiMinat = collect([
            ['kode' => 'R', 'nama' => 'Realistic'],
            ['kode' => 'I', 'nama' => 'Investigative'],
            ['kode' => 'A', 'nama' => 'Artistic'],
            ['kode' => 'S', 'nama' => 'Social'],
            ['kode' => 'E', 'nama' => 'Enterprising'],
            ['kode' => 'C', 'nama' => 'Conventional'],
        ])->map(fn (array $dimensi): array => $dimensi + [
            'nilai' => (float) ($skorRataRata[$dimensi['kode']] ?? 0),
        ]);

        $antrean = (clone $pengajuanQuery)
            ->with(['siswa.riwayatKelasAktif.kelas'])
            ->whereIn('status', ['menunggu', 'diproses', 'dijadwalkan'])
            ->orderBy('created_at')
            ->limit(6)
            ->get();
        $jadwalBimbingan = (clone $pengajuanQuery)
            ->with('siswa')
            ->where('status', 'dijadwalkan')
            ->whereDate('tanggal_bimbingan', '>=', today())
            ->orderBy('tanggal_bimbingan')
            ->orderBy('jam_bimbingan')
            ->limit(4)
            ->get();

        $aktivitasTes = (clone $sesiSelesaiQuery)
            ->join('siswa', 'siswa.id_siswa', '=', 'riwayat.id_siswa')
            ->whereNotNull('sesi_tes.waktu_selesai')
            ->orderByDesc('sesi_tes.waktu_selesai')
            ->limit(5)
            ->get(['siswa.nama', 'kelas.nama as kelas', 'sesi_tes.waktu_selesai as waktu', 'sesi_tes.id_sesi_tes'])
            ->map(fn (object $item): array => [
                'judul' => 'Tes RIASEC selesai',
                'detail' => $item->nama.' · '.$item->kelas,
                'waktu' => $item->waktu,
                'jenis' => 'tes_selesai',
            ]);

        $aktivitasBk = (clone $pengajuanQuery)
            ->join('siswa', 'siswa.id_siswa', '=', 'pengajuan_bk.id_siswa')
            ->whereIn('pengajuan_bk.status', ['menunggu', 'diproses', 'dijadwalkan', 'selesai'])
            ->orderByDesc('pengajuan_bk.updated_at')
            ->limit(5)
            ->get([
                'siswa.nama',
                'pengajuan_bk.topik',
                'pengajuan_bk.status',
                'pengajuan_bk.updated_at as waktu',
                'pengajuan_bk.id_pengajuan',
            ])
            ->map(fn (object $item): array => [
                'judul' => 'Pengajuan BK '.str_replace('_', ' ', $item->status),
                'detail' => $item->nama.' · '.$item->topik,
                'waktu' => $item->waktu,
                'jenis' => 'pengajuan',
            ]);
        $aktivitasValidasi = DB::table('log_aktivitas')
            ->where('id_user', $ctx['user']->id_user)
            ->whereIn('aksi', ['validasi_tervalidasi', 'validasi_perlu_revisi'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get(['aksi', 'nama_entitas', 'entitas_id', 'created_at as waktu'])
            ->map(fn (object $item): array => [
                'judul' => $item->aksi === 'validasi_tervalidasi' ? 'Validasi disetujui' : 'Meminta revisi validasi',
                'detail' => ($item->nama_entitas === 'mapel' ? 'Mapel' : 'Program studi').' · ID '.$item->entitas_id,
                'waktu' => $item->waktu,
                'jenis' => 'validasi',
            ]);

        $aktivitas = $aktivitasTes
            ->concat($aktivitasBk)
            ->concat($aktivitasValidasi)
            ->sortByDesc('waktu')
            ->take(6)
            ->values();
        $greeting = match (now()->hour) {
            0, 1, 2, 3, 4 => 'malam',
            5, 6, 7, 8, 9, 10 => 'pagi',
            11, 12, 13, 14 => 'siang',
            15, 16, 17 => 'sore',
            default => 'malam',
        };

        return view('bk.guru.dashboard', array_merge($ctx, compact(
            'rekapStats',
            'pengajuanStats',
            'menungguValidasi',
            'distribusiMinat',
            'antrean',
            'jadwalBimbingan',
            'aktivitas',
            'tingkatDitugaskan',
            'greeting',
        )));
    }

    /**
     * Rekap Tes RIASEC — query tabel langsung dengan filter dan pagination.
     */
    public function rekapTes(Request $request)
    {
        $ctx = $this->getKonteksGuru();
        $idSemester = $ctx['semester']->id_semester;
        $tingkatDitugaskan = $ctx['penugasan']->pluck('tingkat')->unique()->values()->all();

        $latestTimes = DB::table('sesi_tes')
            ->select('id_riwayat_kelas')
            ->selectRaw('MAX(waktu_selesai) as waktu_selesai')
            ->where('status', 'selesai')
            ->groupBy('id_riwayat_kelas');
        $subLatestTes = DB::table('sesi_tes as latest')
            ->joinSub($latestTimes, 'latest_time', function ($join): void {
                $join->on('latest_time.id_riwayat_kelas', '=', 'latest.id_riwayat_kelas')
                    ->on('latest_time.waktu_selesai', '=', 'latest.waktu_selesai');
            })
            ->select('latest.id_riwayat_kelas')
            ->selectRaw('MAX(latest.id_sesi_tes) as max_sesi_tes')
            ->where('latest.status', 'selesai')
            ->groupBy('latest.id_riwayat_kelas');

        $query = DB::table('riwayat_kelas as rk')
            ->join('siswa as sw', 'sw.id_siswa', '=', 'rk.id_siswa')
            ->join('kelas as k', 'k.id_kelas', '=', 'rk.id_kelas')
            ->leftJoinSub($subLatestTes, 'lt', fn ($join) => $join->on('lt.id_riwayat_kelas', '=', 'rk.id_riwayat_kelas'))
            ->leftJoin('sesi_tes as st', 'st.id_sesi_tes', '=', 'lt.max_sesi_tes')
            ->where('rk.id_semester', $idSemester)
            ->whereIn('k.tingkat', $tingkatDitugaskan)
            ->select(
                'rk.id_riwayat_kelas',
                'sw.nama',
                'sw.nis',
                'k.nama as kelas',
                'k.tingkat',
                'st.id_sesi_tes',
                'st.kode_holland',
                'st.waktu_selesai as tanggal_tes',
                DB::raw('CASE WHEN st.id_sesi_tes IS NOT NULL THEN "Selesai" ELSE "Belum" END as status')
            );

        if ($request->filled('tingkat')) {
            $query->where('k.tingkat', $request->tingkat);
        }

        if ($request->filled('status')) {
            if ($request->status === 'Selesai') {
                $query->whereNotNull('st.id_sesi_tes');
            } else {
                $query->whereNull('st.id_sesi_tes');
            }
        }

        if ($request->filled('kode')) {
            $query->where('st.kode_holland', 'like', '%' . $request->kode . '%');
        }

        if ($request->filled('cari')) {
            $cari = $request->cari;
            $query->where(function ($q) use ($cari) {
                $q->where('sw.nama', 'like', "%{$cari}%")
                    ->orWhere('sw.nis', 'like', "%{$cari}%");
            });
        }

        $data = $query->orderBy('k.nama')->orderBy('sw.nama')->paginate(20)->withQueryString();

        return view('bk.guru.rekap-tes', array_merge($ctx, compact('data')));
    }

    /**
     * Detail hasil tes seorang siswa.
     */
    public function detailTes(int $id)
    {
        $ctx = $this->getKonteksGuru();

        // Cari riwayat kelas
        $tingkatDitugaskan = $ctx['penugasan']->pluck('tingkat')->unique()->values()->all();
        $rk = RiwayatKelas::with(['siswa', 'kelas'])
            ->where('id_semester', $ctx['semester']->id_semester)
            ->whereHas('kelas', fn ($query) => $query->whereIn('tingkat', $tingkatDitugaskan))
            ->findOrFail($id);

        // Ambil sesi tes selesai terakhir
        $sesi = SesiTes::with('hasilTes.dimensi')
            ->where('id_riwayat_kelas', $rk->id_riwayat_kelas)
            ->where('status', 'selesai')
            ->orderByDesc('waktu_selesai')
            ->first();

        $skorMap = [];
        if ($sesi) {
            foreach ($sesi->hasilTes as $ht) {
                $kode = $ht->dimensi->kode ?? '';
                if ($kode) {
                    $skorMap['skor_' . strtolower($kode)] = $ht->skor_normal;
                }
            }
        }

        $rekap = (object) array_merge([
            'id_riwayat_kelas' => $rk->id_riwayat_kelas,
            'nama'             => $rk->siswa->nama ?? '-',
            'nis'              => $rk->siswa->nis ?? '-',
            'kelas'            => $rk->kelas->nama ?? '-',
            'tingkat'          => $rk->kelas->tingkat ?? '-',
            'kode_holland'     => $sesi->kode_holland ?? null,
            'tanggal_tes'      => $sesi?->waktu_mulai ? Carbon::parse($sesi->waktu_mulai)->format('d M Y') : '-',
        ], $skorMap);

        $hasilMinat = null;
        if ($sesi) {
            if (in_array($rk->kelas->tingkat, ['X', 'XI'])) {
                $mapel = $this->rekomService->rekomendasiMapel($sesi->id_sesi_tes);
                $hasilMinat = (object) $mapel->pluck('nama', 'peringkat')->mapWithKeys(fn ($v, $k) => ['rekomendasi_' . $k => $v])->toArray();
            } else {
                $prodi = $this->rekomService->rekomendasiProdi($sesi->id_sesi_tes);
                $hasilMinat = (object) $prodi->pluck('nama', 'peringkat')->mapWithKeys(fn ($v, $k) => ['rekomendasi_' . $k => $v])->toArray();
            }
        }

        $dimensi = DimensiRiasec::all()->keyBy('kode');

        return view('bk.guru.detail-tes', array_merge($ctx, compact('rekap', 'hasilMinat', 'dimensi', 'sesi')));
    }

    /**
     * Daftar pengajuan bimbingan konseling.
     */
    public function pengajuanBk(Request $request)
    {
        $ctx = $this->getKonteksGuru();
        $idSemester = $ctx['semester']->id_semester;

        $query = PengajuanBk::with(['siswa'])
            ->where('id_semester', $idSemester)
            ->where('id_guru', $ctx['guru']->id_guru)
            ->whereIn('status', ['menunggu', 'diproses', 'dijadwalkan']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $cari = $request->search;
            $query->whereHas('siswa', fn ($q) => $q->where('nama', 'like', "%{$cari}%"));
        }

        $data = $query->orderBy('created_at')->paginate(15)->withQueryString();

        return view('bk.guru.pengajuan-bk', array_merge($ctx, compact('data')));
    }

    /**
     * Form tindak lanjut pengajuan.
     */
    public function tindakLanjut(int $id)
    {
        $ctx = $this->getKonteksGuru();

        $pengajuan = PengajuanBk::with(['siswa.riwayatKelasAktif.kelas', 'guru'])
            ->where('id_semester', $ctx['semester']->id_semester)
            ->where('id_guru', $ctx['guru']->id_guru)
            ->findOrFail($id);

        return view('bk.guru.tindak-lanjut', array_merge($ctx, compact('pengajuan')));
    }

    /**
     * Simpan tindak lanjut pengajuan BK.
     */
    public function updatePengajuan(Request $request, int $idPengajuan)
    {
        $ctx = $this->getKonteksGuru();
        $pengajuan = PengajuanBk::query()
            ->where('id_semester', $ctx['semester']->id_semester)
            ->where('id_guru', $ctx['guru']->id_guru)
            ->findOrFail($idPengajuan);

        $validated = $request->validate([
            'status' => ['required', 'in:diproses,dijadwalkan,selesai,ditolak'],
            'jadwal_bimbingan' => ['required_if:status,dijadwalkan', 'nullable', 'date', 'after:now'],
            'balasan' => ['required_if:status,selesai,ditolak', 'nullable', 'string', 'max:2000'],
        ]);

        $data = [
            'status' => $validated['status'],
            'id_guru' => $ctx['guru']->id_guru,
        ];

        if (array_key_exists('balasan', $validated)) {
            $data['balasan'] = $validated['balasan'];
        }

        if (! empty($validated['jadwal_bimbingan'])) {
            $dt = Carbon::parse($validated['jadwal_bimbingan']);
            $data['tanggal_bimbingan'] = $dt->toDateString();
            $data['jam_bimbingan'] = $dt->toTimeString();
        }

        $pengajuan->update($data);

        $pesan = match ($validated['status']) {
            'diproses'    => 'Pengajuan sedang diproses.',
            'dijadwalkan' => 'Jadwal bimbingan berhasil ditetapkan.',
            'selesai'     => 'Bimbingan telah diselesaikan.',
            'ditolak'     => 'Pengajuan telah ditolak.',
            default       => 'Status berhasil diperbarui.',
        };

        return redirect()->route('bk.pengajuan')->with('success', $pesan);
    }

    /**
     * Riwayat bimbingan (selesai + ditolak).
     */
    public function riwayatBimbingan(Request $request)
    {
        $ctx = $this->getKonteksGuru();
        $idSemester = $ctx['semester']->id_semester;

        $query = PengajuanBk::with(['siswa', 'guru'])
            ->where('id_semester', $idSemester)
            ->where('id_guru', $ctx['guru']->id_guru)
            ->whereIn('status', ['selesai', 'ditolak']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $data = $query->orderByDesc('updated_at')->paginate(15)->withQueryString();

        return view('bk.guru.riwayat-bimbingan', array_merge($ctx, compact('data')));
    }

    /**
     * Profil Guru BK.
     */
    public function profil()
    {
        $ctx = $this->getKonteksGuru();

        $logPassword = DB::table('log_aktivitas')
            ->where('id_user', $ctx['user']->id_user)
            ->where('aksi', 'ubah_password')
            ->orderByDesc('created_at')
            ->first();

        return view('bk.guru.profil', array_merge($ctx, compact('logPassword')));
    }

    /**
     * Update profil Guru BK.
     */
    public function updateProfil(Request $request)
    {
        $ctx = $this->getKonteksGuru();

        $request->validate([
            'nama'           => 'required|string|max:100',
            'email'          => 'nullable|email|max:100',
            'no_telepon'     => 'nullable|string|max:25',
            'jadwal_layanan' => 'nullable|string|max:100',
        ]);

        $ctx['guru']->update($request->only('nama', 'email', 'no_telepon'));

        // Jika ada penugasan di semester aktif, perbarui jadwal_layanan
        if ($request->filled('jadwal_layanan')) {
            $penugasan = $ctx['guru']->penugasanBkAktif($ctx['semester']->id_semester)->first();
            if ($penugasan) {
                $penugasan->update(['jadwal_layanan' => $request->jadwal_layanan]);
            }
        }

        return redirect()->route('bk.profil')->with('success', 'Profil berhasil diperbarui.');
    }

    /**
     * Ubah kata sandi Guru BK.
     */
    public function ubahPassword(Request $request)
    {
        $ctx = $this->getKonteksGuru();

        $request->validate([
            'password_lama' => 'required|string',
            'password_baru' => 'required|string|min:8|confirmed',
        ]);

        if (! Hash::check($request->password_lama, $ctx['user']->password)) {
            return back()->withErrors(['password_lama' => 'Kata sandi lama tidak sesuai.']);
        }

        $ctx['user']->update(['password' => Hash::make($request->password_baru)]);

        DB::table('log_aktivitas')->insert([
            'id_user'      => $ctx['user']->id_user,
            'aksi'         => 'ubah_password',
            'nama_entitas' => 'user',
            'entitas_id'   => $ctx['user']->id_user,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        return redirect()->route('bk.profil')->with('success', 'Kata sandi berhasil diubah.');
    }
}
