<?php

namespace App\Http\Controllers;

use App\Exports\RekapNilaiExport;
use App\Http\Requests\RekapNilaiRequest;
use App\Models\PeriodeNilai;
use App\Models\Rombel;
use App\Services\AlokasiTpService;
use App\Services\RekapNilaiService;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RekapNilaiController extends Controller
{
    public function index(RekapNilaiRequest $request, RekapNilaiService $service): JsonResponse
    {
        return $this->rekap($request, $service);
    }

    public function show(RekapNilaiRequest $request, Rombel $rombel, RekapNilaiService $service): JsonResponse
    {
        return $this->rekap($request, $service, $rombel);
    }

    private function rekap(RekapNilaiRequest $request, RekapNilaiService $service, ?Rombel $rombel = null): JsonResponse
    {
        $filters = $request->validated();
        $periode = PeriodeNilai::findOrFail($filters['periode_id']);
        $meta = ['periode' => $periode->only(['id', 'jenis', 'semester_id', 'tingkat'])];
        if ($rombel) {
            $service->pastikanAksesRombel($request->user(), $rombel, $periode);
            $filters['rombel_id'] = $rombel->id;
            $meta['rombel'] = $rombel->only(['id', 'nama']);
            $meta['tp'] = AlokasiTpService::untukRekap($rombel->tp()->get(), $periode->jenis)
                ->map(fn ($tp) => $tp->only(['id', 'nomor_urut', 'deskripsi', 'masuk_sts', 'is_sas']));
        }
        $page = $service->query($request->user(), $periode, $filters)->paginate($filters['per_page'] ?? 20);

        return response()->json(['data' => $service->rows($page->getCollection(), $periode), 'meta' => $meta + [
            'current_page' => $page->currentPage(), 'per_page' => $page->perPage(),
            'total' => $page->total(), 'last_page' => $page->lastPage(),
        ]]);
    }

    public function export(RekapNilaiRequest $request, RekapNilaiService $service): BinaryFileResponse
    {
        abort_unless($request->user()->hasAnyRole(['admin', 'kurikulum']), 403, 'Ekspor semua hanya untuk admin/kurikulum.');
        $filters = $request->validated();
        $periode = PeriodeNilai::findOrFail($filters['periode_id']);
        $rows = $service->rows($service->query($request->user(), $periode, $filters)->get(), $periode);

        return Excel::download(new RekapNilaiExport($rows), 'dkn-periode-'.$periode->id.'.xlsx');
    }
}
