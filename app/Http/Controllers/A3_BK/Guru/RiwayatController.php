<?php

namespace App\Http\Controllers\A3_BK\Guru;

use App\Http\Controllers\A3_BK\Concerns\KonteksGuruBk;
use App\Http\Controllers\Controller;
use App\Models\PengajuanBk;
use Illuminate\Http\Request;

/**
 * Riwayat bimbingan (selesai + ditolak).
 */
class RiwayatController extends Controller
{
    use KonteksGuruBk;

    public function index(Request $request)
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
}
