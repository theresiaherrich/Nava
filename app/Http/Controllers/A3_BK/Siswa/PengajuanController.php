<?php

namespace App\Http\Controllers\A3_BK\Siswa;

use App\Http\Controllers\Controller;
use App\Models\GuruBk;
use App\Models\PengajuanBk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * PengajuanController — pengajuan bimbingan konseling siswa.
 */
class PengajuanController extends Controller
{
    public function index()
    {
        $ctx = $this->getKonteksSiswa();

        $pengajuan = $ctx['riwayat']->pengajuanBk()
            ->with('guruBk.guru')
            ->latest()
            ->get();

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

    public function store(Request $request)
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
