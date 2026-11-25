<?php

namespace App\Services;

use App\Models\NilaiTp;
use App\Models\PeriodeNilai;
use App\Models\Rombel;
use App\Models\SiswaRombel;
use App\Models\Tp;
use Illuminate\Support\Collection;

/**
 * Satu sumber rumus nilai (dipakai input, DKN, rapor, analitik, portal siswa):
 *  - nilai_akhir (rapor semester) = rata-rata semua nilai_tp terisi pada rombel, DIHITUNG bukan disimpan.
 *  - nilai_sts = rata-rata nilai_tp yang diisi pada periode STS.
 *  - nilai_sas = nilai TP dengan urutan terbesar.
 * Nilai 0 sah; null/tidak ada record = belum diisi (tidak dihitung sebagai 0).
 */
class NilaiService
{
    public const PRESISI = 2;

    /**
     * Peserta rombel yang ikut periode (tingkat kelas termasuk tingkat periode), urut nama.
     *
     * @return Collection<int, SiswaRombel>
     */
    public function pesertaPeriode(Rombel $rombel, ?PeriodeNilai $periode = null, ?int $idKelas = null): Collection
    {
        return SiswaRombel::with(['riwayatKelas.siswa:id_siswa,nis,nisn,nama,id_agama', 'riwayatKelas.kelas:id_kelas,nama,tingkat'])
            ->where('id_rombel', $rombel->id_rombel)
            ->whereHas('riwayatKelas', function ($q) use ($periode, $idKelas) {
                $q->when($idKelas, fn ($w) => $w->where('id_kelas', $idKelas));
                if ($periode) {
                    $q->whereHas('kelas', fn ($k) => $k->whereIn('tingkat', $periode->tingkat ?: ['-']));
                }
            })
            ->get()
            ->sortBy(fn (SiswaRombel $p) => mb_strtolower($p->riwayatKelas->siswa->nama))
            ->values();
    }

    /**
     * Nilai per peserta: [id_siswa_rombel => [id_tp => NilaiTp]].
     *
     * @return Collection<int, Collection<int, NilaiTp>>
     */
    public function nilaiPeserta(Collection $idSiswaRombel): Collection
    {
        return NilaiTp::whereIn('id_siswa_rombel', $idSiswaRombel->all() ?: [0])->get()
            ->groupBy('id_siswa_rombel')
            ->map(fn (Collection $g) => $g->keyBy('id_tp'));
    }

    /**
     * @param  Collection<int, Tp>  $tp  TP rombel urut `urutan`
     * @param  Collection<int, NilaiTp>  $nilai  nilai satu peserta, key id_tp
     * @return array{nilai_akhir: ?float, nilai_sts: ?float, nilai_sas: ?float, jumlah_tp: int, jumlah_terisi: int, lengkap: bool}
     */
    public static function hitung(Collection $tp, Collection $nilai, ?int $idPeriodeSts): array
    {
        $tp = $tp->sortBy('urutan')->values();
        $terisi = $tp->map(fn (Tp $t) => $nilai->get($t->id_tp))->filter(fn ($n) => $n !== null && $n->nilai !== null);
        $sts = $terisi->filter(fn (NilaiTp $n) => $idPeriodeSts !== null && (int) $n->id_periode === $idPeriodeSts);
        $tpSas = $tp->last();
        $nilaiSas = $tpSas ? $nilai->get($tpSas->id_tp)?->nilai : null;

        return [
            'nilai_akhir' => self::rata($terisi),
            'nilai_sts' => self::rata($sts),
            'nilai_sas' => $nilaiSas === null ? null : round((float) $nilaiSas, self::PRESISI),
            'jumlah_tp' => $tp->count(),
            'jumlah_terisi' => $terisi->count(),
            'lengkap' => $tp->isNotEmpty() && $terisi->count() === $tp->count(),
        ];
    }

    /**
     * TP yang wajib terisi untuk paket periode:
     *  STS: TP "hanya STS" + TP yang sudah dinilai pada periode STS di rombel ini.
     *  SAS: seluruh TP rombel (rapor semester memakai semua TP).
     *
     * @return Collection<int, Tp>
     */
    public function tpWajib(Rombel $rombel, PeriodeNilai $periode, ?Collection $tp = null): Collection
    {
        $tp ??= $rombel->tp()->get();
        if ($periode->jenis === 'SAS') {
            return $tp->values();
        }

        $dinilai = NilaiTp::where('id_periode', $periode->id_periode)
            ->whereIn('id_tp', $tp->pluck('id_tp')->all() ?: [0])->distinct()->pluck('id_tp')->flip();

        return $tp->filter(fn (Tp $t) => $t->hanya_sts || $dinilai->has($t->id_tp))->values();
    }

    /** @return array{jumlah_siswa:int, jumlah_tp:int, sel_wajib:int, sel_terisi:int, lengkap:bool, siswa_belum_lengkap: list<string>} */
    public function kelengkapan(Rombel $rombel, PeriodeNilai $periode, int $idKelas): array
    {
        $peserta = $this->pesertaPeriode($rombel, $periode, $idKelas);
        $wajib = $this->tpWajib($rombel, $periode);
        $nilai = $this->nilaiPeserta($peserta->pluck('id_siswa_rombel'));

        $terisi = 0;
        $belum = [];
        foreach ($peserta as $p) {
            $n = $nilai->get($p->id_siswa_rombel, collect());
            $isi = $wajib->filter(fn (Tp $t) => $n->get($t->id_tp)?->nilai !== null)->count();
            $terisi += $isi;
            if ($isi < $wajib->count()) {
                $belum[] = $p->riwayatKelas->siswa->nama;
            }
        }
        $selWajib = $peserta->count() * $wajib->count();

        return [
            'jumlah_siswa' => $peserta->count(),
            'jumlah_tp' => $wajib->count(),
            'sel_wajib' => $selWajib,
            'sel_terisi' => $terisi,
            'lengkap' => $selWajib > 0 && $terisi === $selWajib,
            'siswa_belum_lengkap' => $belum,
        ];
    }

    private static function rata(Collection $nilai): ?float
    {
        return $nilai->isEmpty() ? null : round($nilai->avg(fn (NilaiTp $n) => (float) $n->nilai), self::PRESISI);
    }
}
