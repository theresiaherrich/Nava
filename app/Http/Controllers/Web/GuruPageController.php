<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Guru\PaketController;
use App\Http\Controllers\Guru\RombelGuruController;
use App\Http\Controllers\Guru\TpController;
use App\Models\Rombel;
use App\Models\Semester;
use App\Models\Tp;
use App\Services\InputNilaiService;
use App\Services\PeriodeService;
use App\Support\AcademicAccess;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class GuruPageController
{
    public function tp(Request $request, RombelGuruController $guru, TpController $controller): View
    {
        [$semester, $items, $rombel] = $this->pilihan($request, $guru);
        if (! $rombel) {
            return view('academic.kosong', ['title' => 'Kelola Tujuan Pembelajaran']);
        }

        $items = array_map(function (array $item) use ($controller, $rombel) {
            if ($item['id'] === $rombel->id_rombel || $item['id_mapel'] === $rombel->id_mapel) {
                $data = $controller->index(Rombel::findOrFail($item['id']))->getData(true)['data'];
                $item['tp'] = array_map(fn ($t) => $t + ['id' => $t['id_tp'], 'sas' => $t['tp_sas']], $data['tp']);
                $item['terkunci'] = $data['terkunci'];
                $item['maks'] = $data['maks'];
            }

            return $item;
        }, $items);

        return view('guru.tp', [
            'rombel' => array_values($items),
            'aktif' => $rombel->id_rombel,
            'maks' => Tp::MAKS_PER_ROMBEL,
            'semester' => $semester->nama.' · '.$semester->tahunAjaran->nama,
        ]);
    }

    public function nilai(Request $request, RombelGuruController $guru, PeriodeService $periodeService, InputNilaiService $input, PaketController $paket): View
    {
        [$semester, $items, $rombel] = $this->pilihan($request, $guru);
        if (! $rombel) {
            return view('academic.kosong', ['title' => 'Input Nilai']);
        }

        $periodeList = $periodeService->pastikanAda($semester);
        $jenis = $request->query('periode') === 'sts' ? 'STS' : ($request->query('periode') === 'semester'
            ? 'SAS' : ($periodeList->firstWhere('status_efektif', 'dibuka')?->jenis ?? 'STS'));
        $periode = $periodeList->firstWhere('jenis', $jenis);
        $paketRequest = Request::create('/', 'GET', ['id_periode' => $periode->id_periode]);
        $paketData = $paket->index($paketRequest, $rombel)->getData(true)['data'];
        $grid = $input->konteks($rombel, $periode, null);

        return view('guru.input-nilai', [
            'rombel' => array_values($items),
            'aktif' => collect($items)->firstWhere('id', $rombel->id_rombel),
            'grid' => $grid,
            'paketData' => $paketData,
            'periode' => $jenis === 'STS' ? 'sts' : 'semester',
        ]);
    }

    private function pilihan(Request $request, RombelGuruController $guru): array
    {
        $semester = Semester::aktif();
        if (! $semester) {
            return [null, [], null];
        }
        AcademicAccess::guru();
        $items = array_map(fn ($r) => [
            'id' => $r['id_rombel'],
            'kelas' => $r['nama'],
            'judul' => $r['nama'],
            'id_mapel' => $r['mapel']['id_mapel'],
            'mapel' => $r['mapel']['nama'],
            'jumlah_tp' => $r['jumlah_tp'],
        ], $guru->index(Request::create('/', 'GET', ['id_semester' => $semester->id_semester]))->getData(true)['data']);
        $id = $request->integer('rombel') ?: ($items[0]['id'] ?? null);
        abort_if($id && ! in_array($id, array_column($items, 'id'), true), 403);

        return [$semester, $items, $id ? Rombel::findOrFail($id) : null];
    }
}
