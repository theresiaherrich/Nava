<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\RiwayatKelas;
use App\Models\Semester;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class DashboardOperatorController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $semester = Semester::aktif()?->load('tahunAjaran');
        $siswaAktif = Siswa::where('status', 'aktif')->count();
        $berkelas = $semester ? RiwayatKelas::where('id_semester', $semester->id_semester)->count() : 0;

        return $this->ok([
            'semester_aktif' => $semester ? ['id_semester' => $semester->id_semester, 'nama' => $semester->nama, 'tahun_ajaran' => $semester->tahunAjaran->nama] : null,
            'jumlah_siswa' => $siswaAktif,
            'jumlah_guru' => Guru::where('aktif', true)->count(),
            'jumlah_kelas' => $semester ? Kelas::where('id_tahun_ajaran', $semester->id_tahun_ajaran)->count() : 0,
            'akun_belum_aktif' => User::where('status_akun', 'belum_aktif')->count(),
            'siswa_tanpa_akun' => Siswa::where('status', 'aktif')->whereNull('id_user')->count(),
            'guru_tanpa_akun' => Guru::where('aktif', true)->whereNull('id_user')->count(),
            'siswa_tanpa_kelas' => max(0, $siswaAktif - $berkelas),
        ]);
    }
}
