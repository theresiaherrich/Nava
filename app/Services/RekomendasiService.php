<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * RekomendasiService — mengambil data rekomendasi dari view database.
 * Semua perhitungan Cosine Similarity dilakukan oleh view MySQL.
 */
class RekomendasiService
{
    /**
     * Rekomendasi mapel (5 teratas) untuk sesi tes tertentu.
     * Digunakan untuk kelas X dan XI.
     * View: v_rekomendasi_mapel JOIN mapel JOIN profil_mapel.
     */
    public function rekomendasiMapel(int $idSesiTes): Collection
    {
        return DB::table('v_rekomendasi_mapel as rm')
            ->join('mapel as m', 'm.id_mapel', '=', 'rm.id_mapel')
            ->join('profil_mapel as pm', 'pm.id_mapel', '=', 'rm.id_mapel')
            ->where('rm.id_sesi_tes', $idSesiTes)
            ->orderBy('rm.peringkat')
            ->select(
                'rm.peringkat',
                'rm.skor',
                'm.id_mapel',
                'm.nama',
                'm.kode',
                'pm.rumpun',
                'pm.kode_holland',
                'pm.deskripsi',
            )
            ->get();
    }

    /**
     * Rekomendasi program studi (5 teratas) untuk sesi tes tertentu.
     * Digunakan untuk kelas XII.
     * View: v_rekomendasi_prodi JOIN program_studi.
     */
    public function rekomendasiProdi(int $idSesiTes): Collection
    {
        return DB::table('v_rekomendasi_prodi as rp')
            ->join('program_studi as ps', 'ps.id_prodi', '=', 'rp.id_prodi')
            ->where('rp.id_sesi_tes', $idSesiTes)
            ->orderBy('rp.peringkat')
            ->select(
                'rp.peringkat',
                'rp.skor',
                'ps.id_prodi',
                'ps.nama',
                'ps.kategori',
                'ps.deskripsi',
                'ps.kode_holland',
            )
            ->get();
    }

    /**
     * Profesi terkait suatu program studi.
     */
    public function profesiProdi(int $idProdi): Collection
    {
        return DB::table('profesi_prodi as pp')
            ->join('profesi as pf', 'pf.id_profesi', '=', 'pp.id_profesi')
            ->where('pp.id_prodi', $idProdi)
            ->select('pf.id_profesi', 'pf.nama', 'pf.deskripsi')
            ->get();
    }

    /**
     * Kesesuaian mapel pilihan dengan profil siswa (kelas XI).
     * View: v_kecocokan_mapel JOIN mapel JOIN profil_mapel.
     */
    public function kecocokanMapel(int $idSesiTes): Collection
    {
        return DB::table('v_kecocokan_mapel as km')
            ->join('mapel as m', 'm.id_mapel', '=', 'km.id_mapel')
            ->join('profil_mapel as pm', 'pm.id_mapel', '=', 'km.id_mapel')
            ->where('km.id_sesi_tes', $idSesiTes)
            ->orderByDesc('km.skor_kecocokan')
            ->select('km.skor_kecocokan', 'm.id_mapel', 'm.nama', 'm.kode', 'pm.rumpun', 'pm.kode_holland')
            ->limit(5)
            ->get();
    }

    /**
     * Program studi relevan untuk suatu mapel (kelas XI — Mendukung Studi Lanjut).
     * View: v_prodi_relevan_mapel JOIN program_studi.
     */
    public function prodiRelevanMapel(int $idMapel): Collection
    {
        return DB::table('v_prodi_relevan_mapel as prm')
            ->join('program_studi as ps', 'ps.id_prodi', '=', 'prm.id_prodi')
            ->where('prm.id_mapel', $idMapel)
            ->orderByDesc('prm.skor_relevansi')
            ->select('prm.skor_relevansi', 'ps.id_prodi', 'ps.nama', 'ps.kategori', 'ps.kode_holland')
            ->limit(3)
            ->get();
    }

    /**
     * Riwayat semua tes siswa dari v_riwayat_tes.
     */
    public function riwayatTes(int $idSiswa): Collection
    {
        return DB::table('v_riwayat_tes')
            ->where('id_siswa', $idSiswa)
            ->orderByDesc('waktu_selesai')
            ->get();
    }
}
