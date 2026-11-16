<?php

namespace App\Http\Controllers\A3_BK\Admin;

use App\Http\Controllers\Controller;
use App\Models\DimensiRiasec;
use App\Models\Mapel;
use App\Models\Profesi;
use App\Models\ProgramStudi;
use App\Models\Semester;
use App\Models\SoalRiasec;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * DashboardController — halaman utama area Administrator.
 */
class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'jenjang' => ['nullable', 'in:X,XII'],
        ]);
        $semester = Semester::query()
            ->where('aktif', true)
            ->with('tahunAjaran')
            ->orderByDesc('id_semester')
            ->first();

        $stats = [
            'pertanyaan_aktif' => SoalRiasec::where('aktif', true)->count(),
            'dimensi' => DimensiRiasec::count(),
            'mapel' => Mapel::where('is_pilihan', true)->where('aktif', true)->count(),
            'prodi' => ProgramStudi::where('aktif', true)->count(),
        ];

        $dimensions = DimensiRiasec::query()
            ->orderBy('id_dimensi')
            ->get(['kode', 'nama']);

        $scoreQuery = DB::table('hasil_tes_riasec as hasil')
            ->join('dimensi_riasec as dimensi', 'dimensi.id_dimensi', '=', 'hasil.id_dimensi')
            ->join('sesi_tes as sesi', 'sesi.id_sesi_tes', '=', 'hasil.id_sesi_tes')
            ->join('riwayat_kelas as riwayat', 'riwayat.id_riwayat_kelas', '=', 'sesi.id_riwayat_kelas')
            ->join('kelas', 'kelas.id_kelas', '=', 'riwayat.id_kelas')
            ->where('sesi.status', 'selesai')
            ->groupBy('dimensi.kode')
            ->selectRaw('dimensi.kode as kode, ROUND(AVG(hasil.skor_normal), 1) as rata_rata');

        if ($semester) {
            $scoreQuery->where('riwayat.id_semester', $semester->id_semester);
        } else {
            $scoreQuery->whereRaw('1 = 0');
        }

        if (! empty($validated['jenjang'])) {
            $scoreQuery->where('kelas.tingkat', $validated['jenjang']);
        }

        $averageScores = $scoreQuery->pluck('rata_rata', 'kode');
        $colors = [
            'R' => ['bar' => '#0ea5e9', 'track' => '#e0f2fe'],
            'I' => ['bar' => '#6366f1', 'track' => '#e0e7ff'],
            'A' => ['bar' => '#a855f7', 'track' => '#f3e8ff'],
            'S' => ['bar' => '#ec4899', 'track' => '#fce7f3'],
            'E' => ['bar' => '#f59e0b', 'track' => '#fef3c7'],
            'C' => ['bar' => '#10b981', 'track' => '#d1fae5'],
        ];
        $distribusi = $dimensions->map(fn (DimensiRiasec $dimensi): array => [
            'kode' => $dimensi->kode,
            'nama' => $dimensi->nama,
            'nilai' => (float) ($averageScores[$dimensi->kode] ?? 0),
            'warna' => $colors[$dimensi->kode] ?? ['bar' => '#64748b', 'track' => '#e2e8f0'],
        ]);

        $activities = $this->recentActivities($semester?->id_semester);
        $greeting = match (now()->hour) {
            0, 1, 2, 3, 4 => 'malam',
            5, 6, 7, 8, 9, 10 => 'pagi',
            11, 12, 13, 14 => 'siang',
            15, 16, 17 => 'sore',
            default => 'malam',
        };

        return view('bk.admin.dashboard', compact(
            'stats',
            'semester',
            'distribusi',
            'activities',
            'greeting',
            'validated',
        ));
    }

    /**
     * @return Collection<int, array{judul: string, detail: string, waktu: mixed, warna: string}>
     */
    private function recentActivities(?int $semesterId): Collection
    {
        $entityLabels = [
            'mapel' => 'Mapel pilihan',
            'program_studi' => 'Program studi',
            'profesi' => 'Profesi',
            'soal_riasec' => 'Pertanyaan RIASEC',
            'dimensi_riasec' => 'Dimensi RIASEC',
        ];
        $actionLabels = [
            'ditambahkan' => 'ditambahkan',
            'diperbarui' => 'diperbarui',
            'dihapus' => 'dihapus',
            'diaktifkan' => 'diaktifkan',
            'dinonaktifkan' => 'dinonaktifkan',
            'validasi_tervalidasi' => 'divalidasi',
            'validasi_perlu_revisi' => 'diminta revisi',
        ];

        $loggedActivities = DB::table('log_aktivitas')
            ->whereIn('nama_entitas', array_keys($entityLabels))
            ->orderByDesc('created_at')
            ->limit(8)
            ->get()
            ->map(function (object $activity) use ($actionLabels, $entityLabels): array {
                $entity = $entityLabels[$activity->nama_entitas] ?? $activity->nama_entitas;
                $action = $actionLabels[$activity->aksi] ?? $activity->aksi;

                return [
                    'judul' => $entity.' '.$action,
                    'detail' => 'ID '.$activity->entitas_id,
                    'waktu' => $activity->created_at,
                    'warna' => 'blue',
                ];
            });

        $completedTests = collect();
        if ($semesterId !== null) {
            $completedTests = DB::table('sesi_tes as sesi')
                ->join('riwayat_kelas as riwayat', 'riwayat.id_riwayat_kelas', '=', 'sesi.id_riwayat_kelas')
                ->join('siswa', 'siswa.id_siswa', '=', 'riwayat.id_siswa')
                ->join('kelas', 'kelas.id_kelas', '=', 'riwayat.id_kelas')
                ->where('riwayat.id_semester', $semesterId)
                ->where('sesi.status', 'selesai')
                ->whereNotNull('sesi.waktu_selesai')
                ->orderByDesc('sesi.waktu_selesai')
                ->limit(8)
                ->get(['siswa.nama', 'kelas.nama as kelas', 'sesi.waktu_selesai'])
                ->map(fn (object $test): array => [
                    'judul' => 'Tes RIASEC selesai',
                    'detail' => $test->nama.' · '.$test->kelas,
                    'waktu' => $test->waktu_selesai,
                    'warna' => 'green',
                ]);
        }

        return $loggedActivities
            ->concat($completedTests)
            ->sortByDesc('waktu')
            ->take(6)
            ->values();
    }
}
