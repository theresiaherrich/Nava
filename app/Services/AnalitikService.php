<?php

namespace App\Services;

use App\Models\NilaiTp;
use App\Models\PeriodeNilai;
use App\Models\RiwayatKelas;
use App\Models\Siswa;
use App\Models\SiswaRombel;
use App\Models\Tp;
use Illuminate\Support\Collection;

/**
 * Analitik akademik (fitur unggulan A2). Semua agregasi dihitung di server dari nilai_tp
 * dengan rumus yang sama seperti rapor (NilaiService). Pembatasan akses ada di controller.
 */
class AnalitikService
{
    /** Batas histogram sebaran nilai. */
    public const RENTANG = [[0, 59.99, '<60'], [60, 69.99, '60-69'], [70, 79.99, '70-79'], [80, 89.99, '80-89'], [90, 100, '90-100']];

    public function __construct(private NilaiService $nilai) {}

    /**
     * Tren nilai siswa antarperiode/semester: rata-rata STS & akhir semester, serta per mapel.
     *
     * @param  list<int>|null  $hanyaRombel  batasi ke rombel tertentu (mis. guru hanya mapel yang diampu)
     */
    public function trenSiswa(Siswa $siswa, ?array $hanyaRombel = null): array
    {
        $anggota = RiwayatKelas::with(['semester.tahunAjaran', 'kelas:id_kelas,nama'])->where('id_siswa', $siswa->id_siswa)
            ->get()->sortBy(fn ($a) => [$a->semester->tahunAjaran->nama, $a->semester->nama])->values();

        $titik = [];
        $perMapel = [];
        foreach ($anggota as $a) {
            $sts = PeriodeNilai::where('id_semester', $a->id_semester)->where('jenis', 'STS')->value('id_periode');
            $baris = $this->baris(SiswaRombel::where('id_riwayat_kelas', $a->id_riwayat_kelas)
                ->when($hanyaRombel !== null, fn ($q) => $q->whereIn('id_rombel', $hanyaRombel ?: [0]))->get(), $sts ? (int) $sts : null);
            $label = "{$a->semester->tahunAjaran->nama} ".ucfirst(strtolower($a->semester->nama));

            $titik[] = [
                'semester' => $label, 'id_semester' => $a->id_semester, 'kelas' => $a->kelas->nama,
                'rata_sts' => self::rata($baris->pluck('hasil.nilai_sts')),
                'rata_akhir' => self::rata($baris->pluck('hasil.nilai_akhir')),
            ];
            foreach ($baris as $b) {
                $perMapel[$b['mapel']][] = ['semester' => $label, 'nilai_sts' => $b['hasil']['nilai_sts'], 'nilai_akhir' => $b['hasil']['nilai_akhir']];
            }
        }

        return [
            'siswa' => $siswa->only(['id_siswa', 'nis', 'nama']),
            'tren' => $titik,
            'per_mapel' => collect($perMapel)->map(fn ($v, $k) => ['mapel' => $k, 'titik' => $v])->values(),
        ];
    }

    /** Sebaran nilai per mapel untuk sekumpulan peserta (kelas atau rombel). */
    public function sebaran(Collection $siswaRombel, PeriodeNilai $periode): array
    {
        $kolom = $periode->jenis === 'STS' ? 'nilai_sts' : 'nilai_akhir';
        $baris = $this->baris($siswaRombel, $this->idSts($periode));

        return $baris->groupBy('mapel')->map(function (Collection $g, string $mapel) use ($kolom) {
            $v = $g->pluck("hasil.{$kolom}")->filter(fn ($x) => $x !== null)->values();

            return ['mapel' => $mapel, 'jenis_nilai' => $kolom] + self::statistik($v) + [
                'histogram' => collect(self::RENTANG)->map(fn ($r) => ['rentang' => $r[2], 'jumlah' => $v->filter(fn ($x) => $x >= $r[0] && $x <= $r[1])->count()])->values(),
            ];
        })->values()->all();
    }

    /** Persentase ketuntasan per mapel (nilai akhir ≥ KKTP mapel) dan per TP (nilai ≥ KKTP TP). */
    public function ketuntasan(Collection $siswaRombel, PeriodeNilai $periode): array
    {
        $baris = $this->baris($siswaRombel, $this->idSts($periode));
        $kolom = $periode->jenis === 'STS' ? 'nilai_sts' : 'nilai_akhir';

        return $baris->groupBy('id_rombel')->map(function (Collection $g) use ($kolom, $periode) {
            $mapel = $g->first()['mapel_model'];
            $tp = $g->first()['tp'];
            $nilaiMapel = $g->pluck("hasil.{$kolom}")->filter(fn ($x) => $x !== null);

            return [
                'id_rombel' => $g->first()['id_rombel'],
                'mapel' => $mapel->nama,
                'kktp' => $mapel->kktp_default,
                'jumlah_dinilai' => $nilaiMapel->count(),
                'persen_tuntas' => self::persen($nilaiMapel->filter(fn ($x) => $x >= $mapel->kktp_default)->count(), $nilaiMapel->count()),
                'per_tp' => $tp->filter(fn (Tp $t) => $periode->jenis === 'SAS' || $g->contains(fn ($b) => (int) $b['nilai']->get($t->id_tp)?->id_periode === (int) $periode->id_periode))
                    ->map(function (Tp $t) use ($g, $mapel) {
                        $v = $g->map(fn ($b) => $b['nilai']->get($t->id_tp)?->nilai)->filter(fn ($x) => $x !== null);
                        $kktp = $t->kktpEfektif($mapel);

                        return ['id_tp' => $t->id_tp, 'urutan' => $t->urutan, 'deskripsi' => $t->deskripsi, 'kktp' => $kktp,
                            'jumlah_dinilai' => $v->count(), 'persen_tuntas' => self::persen($v->filter(fn ($x) => $x >= $kktp)->count(), $v->count())];
                    })->values(),
            ];
        })->values()->all();
    }

    /**
     * Early warning: (a) ada nilai TP di bawah KKTP, (b) nilai turun pada dua asesmen berturut-turut
     * (tiga nilai TP berurutan yang terus menurun). Tingkat "tinggi" bila keduanya terjadi atau ≥ 3 TP di bawah KKTP.
     */
    public function earlyWarning(Collection $siswaRombel, ?int $idSts): array
    {
        return $this->baris($siswaRombel, $idSts)->map(function (array $b) {
            $terisi = $b['tp']->map(fn (Tp $t) => ['tp' => $t, 'n' => $b['nilai']->get($t->id_tp)?->nilai])->filter(fn ($x) => $x['n'] !== null)->values();
            $bawah = $terisi->filter(fn ($x) => $x['n'] < $x['tp']->kktpEfektif($b['mapel_model']));

            $turun = false;
            for ($i = 2; $i < $terisi->count(); $i++) {
                if ($terisi[$i]['n'] < $terisi[$i - 1]['n'] && $terisi[$i - 1]['n'] < $terisi[$i - 2]['n']) {
                    $turun = true;
                    break;
                }
            }
            if ($bawah->isEmpty() && ! $turun) {
                return null;
            }

            return [
                'siswa' => $b['siswa'], 'kelas' => $b['kelas'], 'mapel' => $b['mapel'], 'id_rombel' => $b['id_rombel'],
                'tingkat' => ($bawah->isNotEmpty() && $turun) || $bawah->count() >= 3 ? 'tinggi' : 'sedang',
                'alasan' => array_values(array_filter([
                    $bawah->isNotEmpty() ? $bawah->count().' TP di bawah KKTP' : null,
                    $turun ? 'Nilai turun pada dua asesmen berturut-turut' : null,
                ])),
                'tp_di_bawah_kktp' => $bawah->map(fn ($x) => ['urutan' => $x['tp']->urutan, 'deskripsi' => $x['tp']->deskripsi, 'nilai' => $x['n'], 'kktp' => $x['tp']->kktpEfektif($b['mapel_model'])])->values(),
                'nilai_akhir' => $b['hasil']['nilai_akhir'],
            ];
        })->filter()->sortBy(fn ($w) => [$w['tingkat'] === 'tinggi' ? 0 : 1, $w['siswa']['nama']])->values()->all();
    }

    // ------------------------------------------------------------------ util

    /** Satu baris per siswa × rombel dengan nilai & hasil hitung. */
    private function baris(Collection $siswaRombel, ?int $idSts): Collection
    {
        $siswaRombel = SiswaRombel::with(['rombel.mapel', 'rombel.tp', 'riwayatKelas.siswa:id_siswa,nis,nama', 'riwayatKelas.kelas:id_kelas,nama'])
            ->whereKey($siswaRombel->pluck('id_siswa_rombel')->all() ?: [0])->get();
        $nilai = NilaiTp::whereIn('id_siswa_rombel', $siswaRombel->pluck('id_siswa_rombel')->all() ?: [0])->get()
            ->groupBy('id_siswa_rombel')->map(fn ($g) => $g->keyBy('id_tp'));

        return $siswaRombel->map(fn (SiswaRombel $p) => [
            'id_rombel' => $p->id_rombel,
            'mapel' => $p->rombel->mapel->nama,
            'mapel_model' => $p->rombel->mapel,
            'tp' => $p->rombel->tp,
            'siswa' => $p->riwayatKelas->siswa->only(['id_siswa', 'nis', 'nama']),
            'kelas' => $p->riwayatKelas->kelas->nama,
            'nilai' => $nilai->get($p->id_siswa_rombel, collect()),
            'hasil' => NilaiService::hitung($p->rombel->tp, $nilai->get($p->id_siswa_rombel, collect()), $idSts),
        ]);
    }

    private function idSts(PeriodeNilai $periode): ?int
    {
        $id = $periode->jenis === 'STS' ? $periode->id_periode
            : PeriodeNilai::where('id_semester', $periode->id_semester)->where('jenis', 'STS')->value('id_periode');

        return $id ? (int) $id : null;
    }

    /** n, rerata, simpangan baku populasi, min, maks, median. */
    public static function statistik(Collection $v): array
    {
        $n = $v->count();
        if ($n === 0) {
            return ['n' => 0, 'rerata' => null, 'simpangan_baku' => null, 'min' => null, 'maks' => null, 'median' => null];
        }
        $rerata = $v->avg();

        return [
            'n' => $n,
            'rerata' => round($rerata, 2),
            'simpangan_baku' => round(sqrt($v->map(fn ($x) => ($x - $rerata) ** 2)->sum() / $n), 2),
            'min' => round($v->min(), 2),
            'maks' => round($v->max(), 2),
            'median' => round((float) $v->median(), 2),
        ];
    }

    private static function rata(Collection $v): ?float
    {
        $v = $v->filter(fn ($x) => $x !== null);

        return $v->isEmpty() ? null : round($v->avg(), 2);
    }

    private static function persen(int $a, int $b): ?float
    {
        return $b === 0 ? null : round($a / $b * 100, 1);
    }
}
