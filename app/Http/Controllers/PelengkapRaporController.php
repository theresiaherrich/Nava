<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PelengkapRaporController extends Controller
{
    public function index(Request $request)
    {
        return view('pelengkap-rapor.index', $this->dataHalaman($request));
    }

    /**
     * Simpan catatan satu siswa (dipanggil otomatis saat mengetik).
     */
    public function simpanCatatan(Request $request, $siswa)
    {
        $request->validate([
            'catatan' => 'nullable|string|max:300',
        ]);

        // TODO: simpan catatan wali kelas untuk siswa $siswa

        return response()->json([
            'ok'    => true,
            'waktu' => now()->format('H.i'),
        ]);
    }

    /**
     * Simpan seluruh catatan sekaligus (tombol "Simpan").
     */
    public function simpan(Request $request)
    {
        $request->validate([
            'catatan'   => 'nullable|array',
            'catatan.*' => 'nullable|string|max:300',
        ]);

        // TODO: simpan semua catatan wali kelas

        return back()->with('toast', [
            'type'    => 'success',
            'message' => 'Pelengkap rapor berhasil disimpan.',
        ]);
    }

    /**
     * Susun data untuk view.
     * TODO: ganti setiap nilai kosong dengan query database.
     */
    private function dataHalaman(Request $request): array
    {
        return [
            'kelas'              => null,
            'periodeAktif'       => $request->query('periode', 'sts'),
            'statusFilter'       => $request->query('status', 'semua'),
            'search'             => $request->query('q', ''),
            'siswa'              => collect(), // id, nisn, nama, sakit, izin, alpa, ekskul[['nama','nilai']], catatan
            'totalSiswa'         => 0,
            'catatanTerisi'      => 0,
            'ekskulTerisi'       => 0,
            'lengkap'            => 0,
            'kehadiranTerisi'    => false,
            'kehadiranSumber'    => null,
            'kehadiranWaktu'     => null,
            'templateCepat'      => [],
            'terkunci'           => false,
            'infoBanner'         => null, // ['tipe' => 'info|success|warning', 'judul' => '...', 'teks' => '...']
            'terakhirTersimpan'  => null,
        ];
    }
}