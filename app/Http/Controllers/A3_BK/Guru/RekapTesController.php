<?php

namespace App\Http\Controllers\A3_BK\Guru;

use App\Http\Controllers\A3_BK\Concerns\KonteksGuruBk;
use App\Http\Controllers\Controller;
use App\Models\DimensiRiasec;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Rekap Tes RIASEC — daftar dan detail hasil tes siswa.
 */
class RekapTesController extends Controller
{
    use KonteksGuruBk;

    /**
     * Tabel v_rekap_tes_bk dengan filter dan pagination.
     */
    public function index(Request $request)
    {
        $ctx = $this->getKonteksGuru();
        $idSemester = $ctx['semester']->id_semester;

        $query = DB::table('v_rekap_tes_bk')
            ->where('id_semester', $idSemester);

        if ($request->filled('tingkat')) {
            $query->where('tingkat', $request->tingkat);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('kode')) {
            $query->where('kode_holland', 'like', '%'.$request->kode.'%');
        }

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
     * Detail hasil tes seorang siswa.
     */
    public function show(int $id)
    {
        $ctx = $this->getKonteksGuru();

        $rekap = DB::table('v_rekap_tes_bk')
            ->where('id_riwayat_kelas', $id)
            ->first();

        if (! $rekap) {
            abort(404, 'Data tidak ditemukan.');
        }

        $hasilMinat = DB::table('v_hasil_minat_siswa')
            ->where('id_riwayat_kelas', $id)
            ->first();

        $dimensi = DimensiRiasec::all()->keyBy('kode');

        return view('bk.guru.detail-tes', array_merge($ctx, compact('rekap', 'hasilMinat', 'dimensi')));
    }
}
