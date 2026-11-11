<?php

namespace App\Http\Controllers\A3_BK;

use App\Http\Controllers\Controller;
use App\Models\DimensiRiasec;
use App\Models\Guru;
use App\Models\GuruBk;
use App\Models\PengajuanBk;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * GuruBkController â€” semua halaman area Guru BK.
 */
class GuruBkController extends Controller
{
    /**
     * Ambil konteks Guru BK yang sedang login.
     */
    private function getKonteksGuru(): array
    {
        $user = Auth::user();
        $guru = Guru::where('id_user', $user->id_user)->with('guruBk')->firstOrFail();
        $guruBk = $guru->guruBk;

        $semester = Semester::where('aktif', true)
            ->with('tahunAjaran')
            ->orderByDesc('id_semester')
            ->first();

        if (! $semester) {
            abort(503, 'Tidak ada semester aktif saat ini.');
        }

        return compact('user', 'guru', 'guruBk', 'semester');
    }

    /**
     * Dashboard Guru BK â€” data real dari v_rekap_tes_bk dan pengajuan_bk.
     */
    public function dashboard()
    {
        $ctx = $this->getKonteksGuru();
        $idSemester = $ctx['semester']->id_semester;

        // Statistik dari v_rekap_tes_bk (semester aktif)
        $rekapStats = DB::table('v_rekap_tes_bk')
            ->where('id_semester', $idSemester)
            ->selectRaw('
                COUNT(*) as total_siswa,
                SUM(CASE WHEN status = "Selesai" THEN 1 ELSE 0 END) as sudah_tes,
                SUM(CASE WHEN status = "Belum"   THEN 1 ELSE 0 END) as belum_tes
            ')
            ->first();

        // Pengajuan aktif (menunggu + diproses + dijadwalkan)
        $pengajuanAktif = PengajuanBk::whereIn('status', ['menunggu', 'diproses', 'dijadwalkan'])
            ->whereHas('riwayatKelas', fn ($q) => $q->where('id_semester', $idSemester))
            ->count();

        // Distribusi minat (rata-rata skor per dimensi) â€” semester aktif
        $distribusiMinat = DB::table('sesi_tes as st')
            ->join('riwayat_kelas as rk', 'rk.id_riwayat_kelas', '=', 'st.id_riwayat_kelas')
            ->where('rk.id_semester', $idSemester)
            ->selectRaw('
                AVG(st.skor_r) as r,
                AVG(st.skor_i) as i,
                AVG(st.skor_a) as a,
                AVG(st.skor_s) as s,
                AVG(st.skor_e) as e,
                AVG(st.skor_c) as c
            ')
            ->first();

        // Antrean bimbingan (menunggu + diproses + dijadwalkan)
        $antrean = PengajuanBk::with('riwayatKelas.siswa', 'riwayatKelas.kelas')
            ->whereIn('status', ['menunggu', 'diproses', 'dijadwalkan'])
            ->whereHas('riwayatKelas', fn ($q) => $q->where('id_semester', $idSemester))
            ->orderBy('created_at')
            ->limit(10)
            ->get();

        // Aktivitas terbaru (10 terakhir dari pengajuan + sesi_tes semester aktif)
        $aktivitasTes = DB::table('sesi_tes as st')
            ->join('riwayat_kelas as rk', 'rk.id_riwayat_kelas', '=', 'st.id_riwayat_kelas')
            ->join('siswa as sw', 'sw.id_siswa', '=', 'rk.id_siswa')
            ->join('kelas as k', 'k.id_kelas', '=', 'rk.id_kelas')
            ->where('rk.id_semester', $idSemester)
            ->select('sw.nama', 'k.nama as kelas', 'st.waktu_selesai', DB::raw('"tes_selesai" as jenis'))
            ->orderByDesc('st.waktu_selesai')
            ->limit(5)
            ->get();

        $aktivitasBk = DB::table('pengajuan_bk as pb')
            ->join('riwayat_kelas as rk', 'rk.id_riwayat_kelas', '=', 'pb.id_riwayat_kelas')
            ->join('siswa as sw', 'sw.id_siswa', '=', 'rk.id_siswa')
            ->where('rk.id_semester', $idSemester)
            ->selectRaw('sw.nama, pb.topik as kelas, pb.created_at as waktu_selesai, "pengajuan_baru" as jenis')
            ->orderByDesc('pb.created_at')
            ->limit(5)
            ->get();

        $aktivitas = $aktivitasTes->concat($aktivitasBk)
            ->sortByDesc('waktu_selesai')
            ->take(8)
            ->values();

        return view('bk.guru.dashboard', array_merge($ctx, compact(
            'rekapStats',
            'pengajuanAktif',
            'distribusiMinat',
            'antrean',
            'aktivitas',
        )));
    }

    /**
     * Rekap Tes RIASEC â€” tabel v_rekap_tes_bk dengan filter dan pagination.
     */
    public function rekapTes(Request $request)
    {
        $ctx = $this->getKonteksGuru();
        $idSemester = $ctx['semester']->id_semester;

        $query = DB::table('v_rekap_tes_bk')
            ->where('id_semester', $idSemester);

        // Filter jenjang
        if ($request->filled('tingkat')) {
            $query->where('tingkat', $request->tingkat);
        }

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter kode Holland
        if ($request->filled('kode')) {
            $query->where('kode_holland', 'like', '%'.$request->kode.'%');
        }

        // Pencarian nama/NIS
        if ($request->filled('cari')) {
            $cari = $request->cari;
            $query->where(function ($q) use ($cari) {
                $q->where('nama', 'like', "%{$cari}%")
                    ->orWhere('nis', 'like', "%{$cari}%");
            });
        }

        $data = $query->orderBy('kelas')->orderBy('nama')->paginate(20)->withQueryString();

        return view('bk.guru.rekap-tes', array_merge($ctx, compact('data')));
    }

    /**
     * Detail hasil tes seorang siswa (untuk Guru BK).
     */
    public function detailTes(int $idRiwayatKelas)
    {
        $ctx = $this->getKonteksGuru();

        $rekap = DB::table('v_rekap_tes_bk')
            ->where('id_riwayat_kelas', $idRiwayatKelas)
            ->first();

        if (! $rekap) {
            abort(404, 'Data tidak ditemukan.');
        }

        $hasilMinat = DB::table('v_hasil_minat_siswa')
            ->where('id_riwayat_kelas', $idRiwayatKelas)
            ->first();

        $dimensi = DimensiRiasec::all()->keyBy('kode');

        return view('bk.guru.detail-tes', array_merge($ctx, compact('rekap', 'hasilMinat', 'dimensi')));
    }

    /**
     * Pengajuan BK â€” daftar semua pengajuan aktif semester ini.
     */
    public function pengajuanBk(Request $request)
    {
        $ctx = $this->getKonteksGuru();
        $idSemester = $ctx['semester']->id_semester;

        $query = PengajuanBk::with('riwayatKelas.siswa', 'riwayatKelas.kelas', 'guruBk.guru')
            ->whereHas('riwayatKelas', fn ($q) => $q->where('id_semester', $idSemester))
            ->whereIn('status', ['menunggu', 'diproses', 'dijadwalkan']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('cari')) {
            $cari = $request->cari;
            $query->whereHas('riwayatKelas.siswa', fn ($q) => $q->where('nama', 'like', "%{$cari}%"));
        }

        $data = $query->orderBy('created_at')->paginate(15)->withQueryString();

        $guruBkList = GuruBk::with('guru')->where('aktif', true)->get();

        return view('bk.guru.pengajuan-bk', array_merge($ctx, compact('data', 'guruBkList')));
    }

    /**
     * Form tindak lanjut pengajuan BK.
     */
    public function tindakLanjut(int $idPengajuan)
    {
        $ctx = $this->getKonteksGuru();

        $pengajuan = PengajuanBk::with('riwayatKelas.siswa', 'riwayatKelas.kelas', 'guruBk.guru')
            ->findOrFail($idPengajuan);

        $guruBkList = GuruBk::with('guru')->where('aktif', true)->get();

        return view('bk.guru.tindak-lanjut', array_merge($ctx, compact('pengajuan', 'guruBkList')));
    }

    /**
     * Simpan tindak lanjut pengajuan BK.
     */
    public function updatePengajuan(Request $request, int $idPengajuan)
    {
        $ctx = $this->getKonteksGuru();
        $pengajuan = PengajuanBk::findOrFail($idPengajuan);

        $rules = [
            'status' => 'required|in:diproses,dijadwalkan,selesai,ditolak',
            'id_guru' => 'required|exists:guru_bk,id_guru',
        ];

        // Jadwal wajib untuk status dijadwalkan
        if ($request->status === 'dijadwalkan') {
            $rules['jadwal_bimbingan'] = 'required|date';
        }

        // Balasan wajib untuk dijadwalkan, selesai, ditolak
        if (in_array($request->status, ['dijadwalkan', 'selesai', 'ditolak'])) {
            $rules['balasan_bk'] = 'required|string|min:5';
        }

        $request->validate($rules);

        $data = [
            'status' => $request->status,
            'id_guru' => $request->id_guru,
        ];

        if ($request->filled('balasan_bk')) {
            $data['balasan_bk'] = $request->balasan_bk;
        }

        if ($request->filled('jadwal_bimbingan')) {
            $data['jadwal_bimbingan'] = $request->jadwal_bimbingan;
        }

        $pengajuan->update($data);

        $pesan = match ($request->status) {
            'diproses' => 'Pengajuan sedang diproses.',
            'dijadwalkan' => 'Jadwal bimbingan berhasil ditetapkan.',
            'selesai' => 'Bimbingan telah diselesaikan.',
            'ditolak' => 'Pengajuan telah ditolak.',
            default => 'Status berhasil diperbarui.',
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

        $query = PengajuanBk::with('riwayatKelas.siswa', 'riwayatKelas.kelas', 'guruBk.guru')
            ->whereHas('riwayatKelas', fn ($q) => $q->where('id_semester', $idSemester))
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

        // Waktu terakhir ubah password dari log_aktivitas
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
        'nama' => 'required|string|max:100',
        'email' => 'nullable|email|max:100',
        'no_telepon' => 'nullable|string|max:25',
        'jadwal_layanan' => 'nullable|string|max:100',
    ]);

    DB::transaction(function () use ($request, $ctx) {
        $ctx['guru']->forceFill(['nama' => $request->nama])->save();
        $ctx['guruBk']->update($request->only('email', 'no_telepon', 'jadwal_layanan'));
    });

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

        // Catat di log_aktivitas
        DB::table('log_aktivitas')->insert([
            'id_user' => $ctx['user']->id_user,
            'aksi' => 'ubah_password',
            'nama_entitas' => 'user',
            'entitas_id' => $ctx['user']->id_user,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('bk.profil')->with('success', 'Kata sandi berhasil diubah.');
    }
}

