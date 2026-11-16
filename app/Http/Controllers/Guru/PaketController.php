<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Http\Requests\Penilaian\KirimPaketRequest;
use App\Models\Kelas;
use App\Models\PaketNilai;
use App\Models\PeriodeNilai;
use App\Models\Rombel;
use App\Services\NilaiService;
use App\Services\PaketService;
use App\Services\PenilaianAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaketController extends Controller
{
    public function __construct(private PaketService $paket, private NilaiService $nilai) {}

    /** Status & kelengkapan per kelas asal, dipakai dialog konfirmasi "Kirim nilai". */
    public function index(Request $request, Rombel $rombel): JsonResponse
    {
        PenilaianAccess::pastikanPengampu($rombel);
        $request->validate(['id_periode' => ['required', 'integer', 'exists:periode_nilai,id_periode']]);
        $periode = PeriodeNilai::findOrFail($request->integer('id_periode'));

        $kelas = $this->nilai->pesertaPeriode($rombel, $periode)->pluck('riwayatKelas.kelas')->unique('id_kelas')->sortBy('nama');
        $paket = PaketNilai::with('riwayat.user:id_user,nama')->where('id_rombel', $rombel->id_rombel)->where('id_periode', $periode->id_periode)->get()->keyBy('id_kelas');

        return $this->ok($kelas->map(fn (Kelas $k) => [
            'kelas' => $k->only(['id_kelas', 'nama']),
            'id_paket_nilai' => $paket->get($k->id_kelas)?->id_paket_nilai,
            'status' => $paket->get($k->id_kelas)?->status ?? 'draf',
            'koreksi' => (bool) $paket->get($k->id_kelas)?->koreksi,
            'kelengkapan' => $this->nilai->kelengkapan($rombel, $periode, $k->id_kelas),
            'riwayat' => $paket->get($k->id_kelas)?->riwayat ?? [],
        ])->values());
    }

    public function kirim(KirimPaketRequest $request, Rombel $rombel): JsonResponse
    {
        PenilaianAccess::pastikanPengampu($rombel);
        $hasil = $this->paket->kirim(
            $rombel,
            PeriodeNilai::findOrFail($request->integer('id_periode')),
            Kelas::findOrFail($request->integer('id_kelas')),
            $request->boolean('konfirmasi_belum_lengkap'),
        );

        return $this->ok($hasil, $hasil['kelengkapan']['lengkap'] ? 'Nilai dikirim ke wali kelas.' : 'Nilai dikirim dalam keadaan belum lengkap.');
    }
}
