<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $accounts = DB::table('user')
            ->where('role', 'ORANG_TUA')
            ->count();

        $verifiedStudents = DB::table('orang_tua')
            ->where('status_verifikasi', 'verified')
            ->distinct()
            ->count('id_siswa');

        // Skema Nava saat ini belum mempunyai tabel riwayat pengiriman WhatsApp.
        $failedNotifications = null;

        $pendingRelations = DB::table('orang_tua')
            ->where('status_verifikasi', 'pending')
            ->count();

        $recent = DB::table('log_aktivitas as l')
            ->leftJoin('user as u', 'u.id_user', '=', 'l.id_user')
            ->select('l.*', 'u.username')
            ->orderByDesc('l.created_at')
            ->limit(5)
            ->get();

        $integrations = [
            [
                'label' => 'Presensi',
                'detail' => 'Data presensi sekolah',
                'status' => Schema::hasTable('presensi') ? 'Tersedia' : 'Belum tersedia',
            ],
            [
                'label' => 'Akademik',
                'detail' => 'Data akademik dan rapor',
                'status' => Schema::hasTable('nilai_tp') && Schema::hasTable('rapor')
                    ? 'Tersedia'
                    : 'Belum tersedia',
            ],
            [
                'label' => 'Minat-Bakat & Karier',
                'detail' => 'Data asesmen dan rekomendasi',
                'status' => Schema::hasTable('hasil_tes_riasec')
                    ? 'Tersedia'
                    : 'Belum tersedia',
            ],
        ];

        return view('admin.dashboard', compact(
            'accounts',
            'verifiedStudents',
            'failedNotifications',
            'pendingRelations',
            'recent',
            'integrations'
        ));
    }
}
