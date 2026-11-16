<?php

namespace App\Http\Controllers\Master;

use App\Exports\TemplateImporSiswaExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Master\ImporSiswaRequest;
use App\Services\SiswaImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ImporSiswaController extends Controller
{
    public function __construct(private SiswaImportService $impor) {}

    public function template(): BinaryFileResponse
    {
        return Excel::download(new TemplateImporSiswaExport, 'template-impor-siswa.xlsx');
    }

    public function pratinjau(ImporSiswaRequest $request): JsonResponse
    {
        $hasil = $this->impor->pratinjau($request->file('file'));

        return $this->ok($hasil, "{$hasil['valid']} baris valid, ".count($hasil['gagal']).' baris gagal.');
    }

    public function simpan(Request $request): JsonResponse
    {
        $request->validate(['token' => ['required', 'uuid']]);
        $hasil = $this->impor->simpan($request->string('token'));

        return $this->ok($hasil, "{$hasil['dibuat']} siswa diimpor.");
    }
}
