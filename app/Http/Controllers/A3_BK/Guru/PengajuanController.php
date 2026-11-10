<?php

namespace App\Http\Controllers\A3_BK\Guru;

use App\Http\Controllers\A3_BK\Concerns\KonteksGuruBk;
use App\Http\Controllers\Controller;
use App\Models\GuruBk;
use App\Models\PengajuanBk;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Pengajuan BK — daftar, form tindak lanjut, dan simpan tindak lanjut.
 */
class PengajuanController extends Controller
{
    use KonteksGuruBk;

    /**
     * Daftar pengajuan aktif semester ini.
     */
    public function index(Request $request)
    {
        $ctx = $this->getKonteksGuru();
        $idSemester = $ctx['semester']->id_semester;

        $query = PengajuanBk::with('riwayatKelas.siswa', 'riwayatKelas.kelas')
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

        return view('bk.guru.pengajuan-bk', array_merge($ctx, compact('data')));
    }

    /**
     * Form tindak lanjut.
     */
    public function tindakLanjut(int $id)
    {
        $ctx = $this->getKonteksGuru();

        $pengajuan = PengajuanBk::with('riwayatKelas.siswa', 'riwayatKelas.kelas', 'guruBk.guru')
            ->findOrFail($id);

        $guruBkList = GuruBk::with('guru')->where('aktif', true)->get();

        return view('bk.guru.tindak-lanjut', array_merge($ctx, compact('pengajuan', 'guruBkList')));
    }

    /**
     * Simpan tindak lanjut.
     */
    public function update(Request $request, int $id)
    {
        $pengajuan = PengajuanBk::findOrFail($id);

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
            // Input datetime-local mengirim "Y-m-d\TH:i"
            $data['jadwal_bimbingan'] = Carbon::parse($request->jadwal_bimbingan);
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
}
