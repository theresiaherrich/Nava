<?php

namespace App\Http\Controllers\A3_BK\Guru;

use App\Http\Controllers\A3_BK\Concerns\KonteksGuruBk;
use App\Http\Controllers\Controller;
use App\Models\PengajuanBk;
use Illuminate\Support\Facades\DB;

/**
 * Dashboard Guru BK â€” data real dari v_rekap_tes_bk dan pengajuan_bk.
 */
class DashboardController extends Controller
{
    use KonteksGuruBk;

    public function index()
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

        // Aktivitas terbaru (pengajuan + sesi_tes semester aktif)
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
}
