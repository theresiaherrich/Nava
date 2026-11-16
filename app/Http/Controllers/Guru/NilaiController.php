<?php

namespace App\Http\Controllers\Guru;

use App\Exports\RekapRombelExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Penilaian\NilaiItemsRequest;
use App\Models\PeriodeNilai;
use App\Models\Rombel;
use App\Services\InputNilaiService;
use App\Services\PenilaianAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/** Input nilai TP: lihat, pratinjau tempel Excel, simpan draf; rekap rombel. */
class NilaiController extends Controller
{
    public function __construct(private InputNilaiService $input) {}

    public function index(Request $request, Rombel $rombel): JsonResponse
    {
        PenilaianAccess::pastikanPengampu($rombel);
        $request->validate([
            'id_periode' => ['required', 'integer', 'exists:periode_nilai,id_periode'],
            'id_kelas' => ['nullable', 'integer', 'exists:kelas,id_kelas'],
        ]);

        return $this->ok($this->input->konteks($rombel, PeriodeNilai::findOrFail($request->integer('id_periode')), $request->integer('id_kelas') ?: null));
    }

    /** Pratinjau tempel dari Excel: validasi penuh tanpa menyimpan. */
    public function pratinjau(NilaiItemsRequest $request, Rombel $rombel): JsonResponse
    {
        PenilaianAccess::pastikanPengampu($rombel);
        $items = $request->items();
        $this->input->periksa($rombel, PeriodeNilai::findOrFail($request->integer('id_periode')), $items);

        return $this->ok(['jumlah_sel' => count($items), 'items' => $items], 'Semua sel dapat disimpan.');
    }

    /** Simpan draf (autosave satu sel atau hasil tempel). */
    public function simpan(NilaiItemsRequest $request, Rombel $rombel): JsonResponse
    {
        PenilaianAccess::pastikanPengampu($rombel);
        $hasil = $this->input->simpan($rombel, PeriodeNilai::findOrFail($request->integer('id_periode')), $request->items());

        return $this->ok($hasil + ['waktu' => now()->toIso8601String()], 'Draf nilai tersimpan.');
    }

    public function rekap(Rombel $rombel): JsonResponse
    {
        PenilaianAccess::pastikanBolehBacaRombel($rombel);
        $sts = PeriodeNilai::where('id_semester', $rombel->id_semester)->where('jenis', 'STS')->first();

        return $this->ok([
            'rombel' => $rombel->only(['id_rombel', 'nama']),
            'tp' => $rombel->tp()->get(['id_tp', 'urutan', 'deskripsi']),
            'siswa' => $this->input->rekapRombel($rombel, $sts)->values(),
        ]);
    }

    public function ekspor(Rombel $rombel): BinaryFileResponse
    {
        PenilaianAccess::pastikanBolehBacaRombel($rombel);
        $sts = PeriodeNilai::where('id_semester', $rombel->id_semester)->where('jenis', 'STS')->first();

        return Excel::download(
            new RekapRombelExport($rombel->tp()->get(), $this->input->rekapRombel($rombel, $sts)->values()->all()),
            'rekap-'.Str::slug($rombel->nama).'.xlsx',
        );
    }
}
