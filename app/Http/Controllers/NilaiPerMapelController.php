<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NilaiPerMapelController extends Controller
{
    private const PER_PAGE = 9;

    public function index(Request $request)
    {
        $periodeAktif   = $request->query('periode') === 'semester' ? 'semester' : 'sts';
        $q              = trim((string) $request->query('q', ''));
        $page           = max(1, (int) $request->query('page', 1));
        $semesterTerbit = false;

        // Data kosong dulu. Nanti diisi dari database.
        // Format satu mapel:
        // [
        //     'id' => 1, 'nama' => 'Matematika', 'kategori' => 'Wajib',
        //     'nilai' => 86, 'kktp' => 75, 'status' => 'tercapai', // atau 'perlu_penguatan'
        // ]
        $semua = collect();

        $hasil = $q === ''
            ? $semua
            : $semua->filter(fn ($m) => str_contains(mb_strtolower($m['nama'] ?? ''), mb_strtolower($q)))->values();

        $totalMapel    = $semua->count();
        $totalFiltered = $hasil->count();
        $lastPage      = max(1, (int) ceil($totalFiltered / self::PER_PAGE));
        $page          = min($page, $lastPage);

        $mapels = $hasil->forPage($page, self::PER_PAGE)->values();

        return view('nilai-per-mapel.index', compact(
            'periodeAktif',
            'q',
            'semesterTerbit',
            'mapels',
            'totalMapel',
            'page',
            'lastPage'
        ));
    }

    public function show(Request $request, string $id)
    {
        // Data kosong dulu. Nanti diisi dari database berdasarkan $id.
        // Format detail:
        // [
        //     'nama' => 'Matematika', 'kategori' => 'Wajib', 'guru' => 'Siti Rahmawati, S.Pd.',
        //     'nilai' => 86, 'kktp' => 75, 'status' => 'tercapai',
        //     'deskripsi' => '...', 'deskripsi_penguatan' => null,
        //     'tps' => [
        //         ['kode' => 'TP1', 'tujuan' => '...', 'nilai' => 88, 'kktp' => 75, 'capaian' => 'tercapai'],
        //     ],
        // ]
        $detail = null;

        return view('nilai-per-mapel.show', compact('detail', 'id'));
    }
}