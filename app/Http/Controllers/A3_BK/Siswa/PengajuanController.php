<?php

namespace App\Http\Controllers\A3_BK\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\PengajuanBk;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * PengajuanController — pengajuan bimbingan konseling siswa.
 */
class PengajuanController extends Controller
{
    public function index()
    {
        $ctx = $this->getKonteksSiswa();
        $tingkat = $ctx['riwayat']->kelas->tingkat;
        $idSemester = $ctx['semester']->id_semester;

        // Ambil pengajuan siswa di semester ini
        $pengajuan = PengajuanBk::with('guru')
            ->where('id_siswa', $ctx['siswa']->id_siswa)
            ->where('id_semester', $idSemester)
            ->latest('created_at')
            ->get();

        $guruBkList = $this->guruBkUntukSiswa($idSemester, $tingkat);

        $topikList = PengajuanBk::TOPIK_LIST;

        return view('bk.siswa.pengajuan-bk', array_merge($ctx, compact(
            'pengajuan',
            'guruBkList',
            'topikList',
        )));
    }

    public function store(Request $request)
    {
        $ctx = $this->getKonteksSiswa();

        $validated = $request->validate([
            'topik'     => 'required|string|in:' . implode(',', PengajuanBk::TOPIK_LIST),
            'deskripsi' => 'required|string|min:20|max:500',
        ]);

        $guruBk = $this->guruBkUntukSiswa(
            $ctx['semester']->id_semester,
            $ctx['riwayat']->kelas->tingkat,
        )->first();

        if (! $guruBk) {
            abort(503, 'Guru BK untuk jenjang Anda belum ditugaskan pada semester aktif.');
        }

        PengajuanBk::create([
            'id_siswa'    => $ctx['siswa']->id_siswa,
            'id_semester' => $ctx['semester']->id_semester,
            'topik'       => $validated['topik'],
            'deskripsi'   => $validated['deskripsi'],
            'status'      => 'menunggu',
            'id_guru'     => $guruBk->id_guru,
        ]);

        return redirect()->route('siswa.pengajuan-bk')
            ->with('success', 'Pengajuan bimbingan konseling berhasil dikirim.');
    }

    public function riwayat()
    {
        $ctx = $this->getKonteksSiswa();
        $pengajuan = PengajuanBk::with('guru')
            ->where('id_siswa', $ctx['siswa']->id_siswa)
            ->orderByDesc('created_at')
            ->get();

        return view('bk.siswa.riwayat-bimbingan', array_merge($ctx, compact('pengajuan')));
    }

    private function guruBkUntukSiswa(int $idSemester, string $tingkat): Collection
    {
        return Guru::query()
            ->whereHas('penugasanBk', function ($query) use ($idSemester, $tingkat): void {
                $query->where('id_semester', $idSemester)
                    ->where(function ($query) use ($tingkat): void {
                        $query->where('tingkat', $tingkat)->orWhereNull('tingkat');
                    });
            })
            ->orderBy('nama')
            ->get();
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
