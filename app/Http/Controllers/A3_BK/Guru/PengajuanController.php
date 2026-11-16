<?php

namespace App\Http\Controllers\A3_BK\Guru;

use App\Http\Controllers\A3_BK\Concerns\KonteksGuruBk;
use App\Http\Controllers\Controller;
use App\Models\Guru;
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

        $query = PengajuanBk::with(['siswa', 'guru'])
            ->where('id_semester', $idSemester)
            ->whereIn('status', ['menunggu', 'diproses', 'dijadwalkan']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('cari')) {
            $cari = $request->cari;
            $query->whereHas('siswa', fn ($q) => $q->where('nama', 'like', "%{$cari}%"));
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

        $pengajuan = PengajuanBk::with(['siswa', 'guru'])->findOrFail($id);
        $guruBkList = Guru::all();

        return view('bk.guru.tindak-lanjut', array_merge($ctx, compact('pengajuan', 'guruBkList')));
    }

    /**
     * Simpan tindak lanjut.
     */
    public function update(Request $request, int $id)
    {
        $pengajuan = PengajuanBk::findOrFail($id);

        $rules = [
            'status'  => 'required|in:diproses,dijadwalkan,selesai,ditolak',
            'id_guru' => 'required|exists:guru,id_guru',
        ];

        if ($request->status === 'dijadwalkan') {
            $rules['jadwal_bimbingan'] = 'nullable|date';
            $rules['tanggal_bimbingan'] = 'nullable|date';
        }

        if (in_array($request->status, ['dijadwalkan', 'selesai', 'ditolak'])) {
            $rules['balasan'] = 'nullable|string';
            $rules['balasan_bk'] = 'nullable|string';
        }

        $request->validate($rules);

        $data = [
            'status'  => $request->status,
            'id_guru' => $request->id_guru,
        ];

        $balasan = $request->input('balasan', $request->input('balasan_bk'));
        if ($balasan) {
            $data['balasan'] = $balasan;
        }

        if ($request->filled('jadwal_bimbingan')) {
            $dt = Carbon::parse($request->jadwal_bimbingan);
            $data['tanggal_bimbingan'] = $dt->toDateString();
            $data['jam_bimbingan'] = $dt->toTimeString();
        } elseif ($request->filled('tanggal_bimbingan')) {
            $data['tanggal_bimbingan'] = $request->tanggal_bimbingan;
            if ($request->filled('jam_bimbingan')) {
                $data['jam_bimbingan'] = $request->jam_bimbingan;
            }
        }

        $pengajuan->update($data);

        $pesan = match ($request->status) {
            'diproses'    => 'Pengajuan sedang diproses.',
            'dijadwalkan' => 'Jadwal bimbingan berhasil ditetapkan.',
            'selesai'     => 'Bimbingan telah diselesaikan.',
            'ditolak'     => 'Pengajuan telah ditolak.',
            default       => 'Status berhasil diperbarui.',
        };

        return redirect()->route('bk.pengajuan')->with('success', $pesan);
    }
}
