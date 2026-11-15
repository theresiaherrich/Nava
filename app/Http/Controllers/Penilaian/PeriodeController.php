<?php

namespace App\Http\Controllers\Penilaian;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\PaketNilai;
use App\Models\PeriodeNilai;
use App\Services\PeriodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PeriodeController extends Controller
{
    public function __construct(private PeriodeService $periode) {}

    public function index(Request $request): JsonResponse
    {
        $semester = $this->semesterDari($request)->load('tahunAjaran');

        $periode = PeriodeNilai::where('id_semester', $semester->id_semester)
            ->orderBy('tingkat')
            ->orderBy('jenis')
            ->get();

        return response()->json(['semester' => $semester, 'periode' => $periode]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_semester' => ['required', 'exists:semester,id_semester'],
            'tingkat' => ['required', Rule::in(['X', 'XI', 'XII'])],
            'jenis' => ['required', Rule::in(['STS', 'SAS'])],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
        ]);

        return response()->json($this->periode->buat($data)->load('semester'), 201);
    }

    public function ubahJadwal(Request $request, PeriodeNilai $periode): JsonResponse
    {
        $data = $request->validate([
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
        ]);

        return response()->json($this->periode->ubahJadwal($periode, $data['tanggal_mulai'], $data['tanggal_selesai']));
    }

    public function ubahStatus(Request $request, PeriodeNilai $periode): JsonResponse
    {
        // "terkunci" hanya dihasilkan otomatis oleh penguncian kelas, tidak diset manual.
        $data = $request->validate(['status' => ['required', Rule::in(['draf', 'dibuka', 'ditutup'])]]);

        return response()->json($this->periode->ubahStatus($periode, $data['status']));
    }

    /** Data untuk halaman "Periode & Penguncian": status per kelas dan progres mapel. */
    public function ringkasan(PeriodeNilai $periode): JsonResponse
    {
        return response()->json([
            'periode' => $periode,
            'kelas' => $this->periode->ringkasan($periode),
        ]);
    }

    public function kunciKelas(PeriodeNilai $periode, Kelas $kelas): JsonResponse
    {
        $jumlah = $this->periode->kunciKelas($periode, $kelas);

        return response()->json([
            'message' => "Kelas {$kelas->nama} dikunci ({$jumlah} mapel). Rapor belum diterbitkan.",
            'jumlah_mapel' => $jumlah,
        ]);
    }

    public function kunciSemuaSiap(PeriodeNilai $periode): JsonResponse
    {
        $kelas = $this->periode->kunciSemuaSiap($periode);

        return response()->json([
            'message' => count($kelas) . ' kelas dikunci. Rapor belum diterbitkan.',
            'kelas' => $kelas,
        ]);
    }

    public function bukaKoreksi(Request $request, PaketNilai $paket): JsonResponse
    {
        $data = $request->validate(['alasan' => ['required', 'string', 'min:10', 'max:500']]);

        $paket = $this->periode->bukaKoreksi($paket, $data['alasan']);

        return response()->json(['message' => 'Koreksi dibuka. Guru dapat mengubah nilai lalu mengirim ulang.', 'paket' => $paket]);
    }
}
