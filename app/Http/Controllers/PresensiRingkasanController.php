<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\PelengkapRapor;
use App\Models\PeriodeNilai;
use App\Services\RaporAccess;
use App\Services\RaporService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

/**
 * Bagian 4.7: ringkasan presensi (baca saja). A2 tidak menghitung dan tidak membuat tabel presensi.
 * Angka sakit/izin/alpha dibaca dari pelengkap_rapor (kolom yang disiapkan untuk ringkasan kehadiran A1).
 * Selama A1 belum mengisinya, nilainya null ("belum tersedia"), bukan 0.
 */
class PresensiRingkasanController extends Controller
{
    /** GET /presensi-ringkasan?kelas_id=&periode_id= */
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'kelas_id' => ['required', 'integer', 'exists:kelas,id'],
            'periode_id' => ['required', 'integer', 'exists:periode_nilai,id'],
        ]);
        $kelas = Kelas::findOrFail($data['kelas_id']);
        $periode = PeriodeNilai::findOrFail($data['periode_id']);
        abort_unless(RaporAccess::bisaMelihatKelas($request->user(), $kelas->id), 403, 'Anda tidak berhak melihat presensi kelas ini.');
        RaporService::pastikanPeriodeCocok($kelas, $periode);

        $semua = RaporService::siswaKelas($kelas);
        $pelengkap = PelengkapRapor::where('id_periode', $periode->id)->whereIn('id_riwayat_kelas', $semua->pluck('id'))->get()->keyBy('id_riwayat_kelas');

        $siswa = $semua->map(function ($sk) use ($pelengkap) {
            $p = $pelengkap->get($sk->id);

            return [
                'siswa_id' => $sk->siswa_id,
                'nis' => $sk->siswa->nis,
                'nama' => $sk->siswa->nama,
                'sakit' => $p?->sakit,
                'izin' => $p?->izin,
                'alpha' => $p?->alpha,
                'tersedia' => $p !== null && $p->sakit !== null && $p->izin !== null && $p->alpha !== null,
            ];
        })->values();

        return response()->json(['data' => [
            'kelas_id' => $kelas->id,
            'periode_id' => $periode->id,
            'sumber' => 'pelengkap_rapor',
            'tabel_presensi_a1_tersedia' => Schema::hasTable('presensi'),
            'ringkasan' => ['total_siswa' => $siswa->count(), 'tersedia' => $siswa->where('tersedia', true)->count()],
            'siswa' => $siswa,
        ]]);
    }
}
