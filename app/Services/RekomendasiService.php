<?php

namespace App\Services;

use App\Models\RekomendasiDuaBelas;
use App\Models\RekomendasiSepuluh;
use App\Models\SesiTes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * RekomendasiService — Hitung dan simpan rekomendasi mapel/prodi.
 *
 * Sesuai ATURAN_A3.md:
 * - Tidak ada view MySQL — semua dihitung di PHP dari tabel hasil_tes_riasec.
 * - Rumus skor kecocokan: (3*skor(h1) + 2*skor(h2) + 1*skor(h3)) / 6 * 100
 *   di mana h1, h2, h3 = 3 kode dari kode_dominan (kode_riasec mapel/prodi).
 * - Mapel kelas X: hanya is_pilihan=1, aktif=1, status_validasi='tervalidasi'.
 * - Pivot profesi: prodi_profesi (bukan profesi_prodi).
 */
class RekomendasiService
{
    /**
     * Hitung dan simpan 5 rekomendasi mapel untuk sesi tes (Kelas X).
     * Simpan ke tabel rekomendasi_sepuluh.
     *
     * @param  SesiTes  $sesi  Harus sudah di-load dengan hasilTes.dimensi
     */
    public function hitungDanSimpanMapel(SesiTes $sesi): Collection
    {
        // Ambil skor normal per kode dimensi
        $skorPerKode = $sesi->load('hasilTes.dimensi')
            ->hasilTes
            ->mapWithKeys(fn ($h) => [$h->dimensi->kode ?? '?' => $h->skor_normal]);

        $kodeArr = str_split(strtoupper($sesi->kode_dominan ?? ''), 1); // ['R','I','A']

        // Ambil semua mapel pilihan aktif tervalidasi
        $mapelList = DB::table('mapel')
            ->where('is_pilihan', 1)
            ->where('aktif', 1)
            ->where('status_validasi', 'tervalidasi')
            ->whereNotNull('kode_riasec')
            ->get();

        // Hitung skor kecocokan per mapel
        $scored = $mapelList->map(function ($mapel) use ($skorPerKode, $kodeArr) {
            $kodeMapel = str_split(strtoupper($mapel->kode_riasec ?? ''), 1);
            $skor = $this->hitungSkorKecocokan($kodeArr, $kodeMapel, $skorPerKode);
            return ['mapel' => $mapel, 'skor' => $skor];
        })->sortByDesc('skor')->take(5)->values();

        // Hapus rekomendasi lama untuk sesi ini, lalu simpan yang baru
        RekomendasiSepuluh::where('id_sesi_tes', $sesi->id_sesi_tes)->delete();

        $insert = $scored->map(fn ($item, $idx) => [
            'id_sesi_tes' => $sesi->id_sesi_tes,
            'id_mapel'    => $item['mapel']->id_mapel,
            'peringkat'   => $idx + 1,
            'skor'        => round($item['skor'], 2),
            'alasan'      => 'Kode RIASEC: ' . ($item['mapel']->kode_riasec ?? '-'),
        ])->toArray();

        if (!empty($insert)) {
            RekomendasiSepuluh::insert($insert);
        }

        return $this->rekomendasiMapel($sesi->id_sesi_tes);
    }

    /**
     * Hitung dan simpan 5 rekomendasi prodi untuk sesi tes (Kelas XII).
     * Simpan ke tabel rekomendasi_dua_belas.
     */
    public function hitungDanSimpanProdi(SesiTes $sesi): Collection
    {
        $skorPerKode = $sesi->load('hasilTes.dimensi')
            ->hasilTes
            ->mapWithKeys(fn ($h) => [$h->dimensi->kode ?? '?' => $h->skor_normal]);

        $kodeArr = str_split(strtoupper($sesi->kode_dominan ?? ''), 1);

        $prodiList = DB::table('program_studi')
            ->where('aktif', 1)
            ->where('status_validasi', 'tervalidasi')
            ->whereNotNull('kode_riasec')
            ->get();

        $scored = $prodiList->map(function ($prodi) use ($skorPerKode, $kodeArr) {
            $kodeProdi = str_split(strtoupper($prodi->kode_riasec ?? ''), 1);
            $skor = $this->hitungSkorKecocokan($kodeArr, $kodeProdi, $skorPerKode);
            return ['prodi' => $prodi, 'skor' => $skor];
        })->sortByDesc('skor')->take(5)->values();

        RekomendasiDuaBelas::where('id_sesi_tes', $sesi->id_sesi_tes)->delete();

        $insert = $scored->map(fn ($item, $idx) => [
            'id_sesi_tes' => $sesi->id_sesi_tes,
            'id_prodi'    => $item['prodi']->id_prodi,
            'peringkat'   => $idx + 1,
            'skor'        => round($item['skor'], 2),
            'alasan'      => 'Kode RIASEC: ' . ($item['prodi']->kode_riasec ?? '-'),
        ])->toArray();

        if (!empty($insert)) {
            RekomendasiDuaBelas::insert($insert);
        }

        return $this->rekomendasiProdi($sesi->id_sesi_tes);
    }

    /**
     * Ambil rekomendasi mapel (sudah tersimpan) untuk sesi tes tertentu.
     */
    public function rekomendasiMapel(int $idSesiTes): Collection
    {
        return RekomendasiSepuluh::with('mapel')
            ->where('id_sesi_tes', $idSesiTes)
            ->orderBy('peringkat')
            ->get()
            ->map(fn ($r) => (object) [
                'peringkat'   => $r->peringkat,
                'skor'        => $r->skor,
                'id_mapel'    => $r->id_mapel,
                'nama'        => $r->mapel->nama ?? '-',
                'kode'        => $r->mapel->kode ?? '-',
                'rumpun'      => $r->mapel->rumpun ?? '-',
                'kode_riasec' => $r->mapel->kode_riasec ?? '-',
                'deskripsi'   => $r->mapel->deskripsi ?? '',
                'alasan'      => $r->alasan,
            ]);
    }

    /**
     * Ambil rekomendasi prodi (sudah tersimpan) untuk sesi tes tertentu.
     */
    public function rekomendasiProdi(int $idSesiTes): Collection
    {
        return RekomendasiDuaBelas::with(['prodi.profesi'])
            ->where('id_sesi_tes', $idSesiTes)
            ->orderBy('peringkat')
            ->get()
            ->map(fn ($r) => (object) [
                'peringkat'   => $r->peringkat,
                'skor'        => $r->skor,
                'id_prodi'    => $r->id_prodi,
                'nama'        => $r->prodi->nama ?? '-',
                'rumpun'      => $r->prodi->rumpun ?? '-',
                'tentang'     => $r->prodi->tentang ?? '',
                'deskripsi'   => $r->prodi->deskripsi ?? '',
                'kode_riasec' => $r->prodi->kode_riasec ?? '-',
                'profesi'     => $r->prodi->profesi ?? collect(),
                'alasan'      => $r->alasan,
            ]);
    }

    /**
     * Profesi terkait suatu program studi (via pivot prodi_profesi).
     */
    public function profesiProdi(int $idProdi): Collection
    {
        return DB::table('prodi_profesi as pp')
            ->join('profesi as pf', 'pf.id_profesi', '=', 'pp.id_profesi')
            ->where('pp.id_prodi', $idProdi)
            ->select('pf.id_profesi', 'pf.nama', 'pf.deskripsi')
            ->get();
    }

    /**
     * Riwayat semua sesi tes selesai untuk seorang siswa.
     */
    public function riwayatTes(int $idSiswa): Collection
    {
        return DB::table('sesi_tes as st')
            ->join('riwayat_kelas as rk', 'rk.id_riwayat_kelas', '=', 'st.id_riwayat_kelas')
            ->join('kelas as k', 'k.id_kelas', '=', 'rk.id_kelas')
            ->join('semester as s', 's.id_semester', '=', 'rk.id_semester')
            ->where('rk.id_siswa', $idSiswa)
            ->where('st.status', 'selesai')
            ->select(
                'st.id_sesi_tes',
                'st.kode_holland as kode_dominan',
                'st.kode_holland',
                'st.waktu_mulai',
                'st.tingkat',
                'k.nama as kelas',
                's.nama as semester',
            )
            ->orderByDesc('st.waktu_mulai')
            ->get();
    }

    // ─── Private Helper ───────────────────────────────────────────────────────

    /**
     * Hitung skor kecocokan antara kode dominan siswa dan kode mapel/prodi.
     *
     * Rumus: (3*skor(h1) + 2*skor(h2) + 1*skor(h3)) / 6
     * di mana h1,h2,h3 adalah kode dari kode_riasec mapel/prodi yang cocok
     * dengan kode dominan siswa.
     *
     * @param  array  $kodeSiswa   ['R','I','A'] — 3 kode dominan siswa
     * @param  array  $kodeTarget  kode mapel/prodi (bisa 1-6 huruf)
     * @param  Collection  $skorPerKode  skor_normal per kode
     */
    private function hitungSkorKecocokan(
        array $kodeSiswa,
        array $kodeTarget,
        $skorPerKode
    ): float {
        $bobot = [3, 2, 1];
        $total = 0.0;
        $maxBobot = 6.0; // 3+2+1

        foreach ($kodeTarget as $i => $kode) {
            if ($i >= 3) break; // maksimal 3 kode
            $b = $bobot[$i] ?? 1;
            $skor = $skorPerKode[$kode] ?? 0;
            $total += $b * $skor;
        }

        return $maxBobot > 0 ? round($total / $maxBobot, 4) : 0.0;
    }
}
