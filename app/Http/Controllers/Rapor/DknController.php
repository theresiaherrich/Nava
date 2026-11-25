<?php

namespace App\Http\Controllers\Rapor;

use App\Exports\DknExport;
use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\PeriodeNilai;
use App\Services\DknService;
use App\Services\RaporAccess;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

/** Rekap DKN per kelas (Kurikulum semua kelas, wali hanya kelasnya) + ekspor Excel/PDF. */
class DknController extends Controller
{
    public function __construct(private DknService $dkn) {}

    public function show(Request $request): JsonResponse
    {
        [$kelas, $periode] = $this->konteks($request);

        return $this->ok($this->dkn->kelas($kelas, $periode));
    }

    public function ekspor(Request $request): Response
    {
        $request->validate(['format' => ['sometimes', 'in:xlsx,pdf']]);
        [$kelas, $periode] = $this->konteks($request);
        $data = $this->dkn->kelas($kelas, $periode);
        $nama = Str::slug("dkn-{$periode->jenis}-{$kelas->nama}");

        if ($request->input('format') === 'pdf') {
            return Pdf::loadView('dkn.pdf', ['dkn' => $data])->setPaper('a4', 'landscape')->download("{$nama}.pdf");
        }

        return Excel::download(new DknExport($data), "{$nama}.xlsx");
    }

    /** @return array{0: Kelas, 1: PeriodeNilai} */
    private function konteks(Request $request): array
    {
        $request->validate([
            'id_kelas' => ['required', 'integer', 'exists:kelas,id_kelas'],
            'id_periode' => ['required', 'integer', 'exists:periode_nilai,id_periode'],
        ]);
        $kelas = Kelas::findOrFail($request->integer('id_kelas'));
        $periode = PeriodeNilai::findOrFail($request->integer('id_periode'));
        RaporAccess::pastikanKelolaKelas($kelas, $periode->id_semester);

        return [$kelas, $periode];
    }
}
