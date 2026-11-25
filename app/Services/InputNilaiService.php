<?php

namespace App\Services;

use App\Exceptions\AturanBisnisException;
use App\Models\NilaiTp;
use App\Models\PaketNilai;
use App\Models\PeriodeNilai;
use App\Models\Rombel;
use App\Models\SiswaRombel;
use App\Models\Tp;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Input nilai TP oleh guru pengampu. Satu sel boleh diisi bila:
 *  1. periode satu semester dengan rombel, tingkat kelas siswa ikut periode;
 *  2. periode sedang dibuka (atau paket dibuka koreksinya oleh Kurikulum) dan tidak dikunci;
 *  3. paket kelas siswa masih draf/perlu_revisi;
 *  4. TP "STS saja" hanya di periode STS;
 *  5. nilai yang sudah ada hanya bisa diubah pada periode tempat ia diisi.
 * Pratinjau dan simpan memakai pemeriksaan yang sama; simpan mengecek ulang di dalam transaksi berkunci.
 */
class InputNilaiService
{
    public function __construct(private NilaiService $nilai, private AuditService $audit) {}

    public function konteks(Rombel $rombel, PeriodeNilai $periode, ?int $idKelas): array
    {
        $this->pastikanPeriodeRombel($rombel, $periode);

        $tp = $rombel->tp()->get();
        $peserta = $this->nilai->pesertaPeriode($rombel, $periode, $idKelas);
        $nilai = $this->nilai->nilaiPeserta($peserta->pluck('id_siswa_rombel'));
        $paket = PaketNilai::where('id_rombel', $rombel->id_rombel)->where('id_periode', $periode->id_periode)->get()->keyBy('id_kelas');
        $mapel = $rombel->mapel;

        return [
            'periode' => $periode,
            'tp' => $tp->map(fn (Tp $t) => $t->only(['id_tp', 'urutan', 'kode', 'deskripsi', 'hanya_sts']) + [
                'kktp' => $t->kktpEfektif($mapel),
                'dapat_diisi_periode_ini' => ! ($t->hanya_sts && $periode->jenis === 'SAS'),
                'tp_sas' => $t->id_tp === $tp->last()?->id_tp,
            ])->values(),
            'paket' => $paket->map(fn (PaketNilai $p) => $p->only(['id_paket_nilai', 'id_kelas', 'status', 'koreksi', 'lengkap']))->values(),
            'peserta' => $peserta->map(function (SiswaRombel $p) use ($nilai, $tp, $periode, $paket) {
                $n = $nilai->get($p->id_siswa_rombel, collect());
                $pk = $paket->get($p->riwayatKelas->id_kelas);
                $kelasBisa = $this->periodeBisaInput($periode, $pk) && ($pk === null || $pk->bisaDiedit());

                return [
                    'id_siswa_rombel' => $p->id_siswa_rombel,
                    'siswa' => $p->riwayatKelas->siswa->only(['id_siswa', 'nis', 'nama']),
                    'kelas' => $p->riwayatKelas->kelas->only(['id_kelas', 'nama']),
                    'nilai' => $tp->mapWithKeys(fn (Tp $t) => [$t->id_tp => [
                        'nilai' => $n->get($t->id_tp)?->nilai,
                        'id_periode' => $n->get($t->id_tp)?->id_periode,
                        'dapat_diedit' => $kelasBisa && $this->selBisa($t, $n->get($t->id_tp), $periode) === null,
                    ]]),
                    'ringkasan' => NilaiService::hitung($tp, $n, $periode->jenis === 'STS' ? $periode->id_periode : null),
                ];
            })->values(),
        ];
    }

    /**
     * Validasi tanpa menyimpan. Melempar 422 berisi errors.items.N.field bila ada sel ditolak.
     *
     * @param  list<array{id_siswa_rombel:int,id_tp:int,nilai:?float}>  $items
     */
    public function periksa(Rombel $rombel, PeriodeNilai $periode, array $items): void
    {
        $this->pastikanPeriodeRombel($rombel, $periode);

        $tp = Tp::where('id_rombel', $rombel->id_rombel)->get()->keyBy('id_tp');
        $peserta = SiswaRombel::with('riwayatKelas.kelas:id_kelas,tingkat')
            ->where('id_rombel', $rombel->id_rombel)
            ->whereKey(array_column($items, 'id_siswa_rombel'))->get()->keyBy('id_siswa_rombel');
        $ada = NilaiTp::whereIn('id_siswa_rombel', $peserta->keys()->all() ?: [0])
            ->whereIn('id_tp', $tp->keys()->all() ?: [0])->get()
            ->keyBy(fn (NilaiTp $n) => $n->id_siswa_rombel.':'.$n->id_tp);
        $paket = PaketNilai::where('id_rombel', $rombel->id_rombel)->where('id_periode', $periode->id_periode)->get()->keyBy('id_kelas');

        $errors = [];
        foreach ($items as $i => $item) {
            $t = $tp->get($item['id_tp']);
            $p = $peserta->get($item['id_siswa_rombel']);

            if (! $t) {
                $errors["items.{$i}.id_tp"] = ['TP tidak ditemukan pada rombel ini.'];

                continue;
            }
            if (! $p) {
                $errors["items.{$i}.id_siswa_rombel"] = ['Peserta tidak ditemukan pada rombel ini.'];

                continue;
            }
            if (! $periode->ikutTingkat($p->riwayatKelas->kelas->tingkat)) {
                $errors["items.{$i}.id_siswa_rombel"] = ['Tingkat kelas siswa tidak ikut periode ini.'];

                continue;
            }
            $pk = $paket->get($p->riwayatKelas->id_kelas);
            if (! $this->periodeBisaInput($periode, $pk)) {
                $errors["items.{$i}.nilai"] = ['Periode penilaian sedang ditutup atau dikunci.'];

                continue;
            }
            if ($pk && ! $pk->bisaDiedit()) {
                $errors["items.{$i}.nilai"] = ["Nilai kelas ini berstatus {$pk->status} dan tidak dapat diubah."];

                continue;
            }
            if ($alasan = $this->selBisa($t, $ada->get($p->id_siswa_rombel.':'.$t->id_tp), $periode)) {
                $errors["items.{$i}.id_tp"] = [$alasan];
            }
        }

        if ($errors !== []) {
            throw new AturanBisnisException('Sebagian sel tidak dapat disimpan.', 'NILAI_DITOLAK', 422, $errors);
        }
    }

    /** @return array{tersimpan:int, dihapus:int} */
    public function simpan(Rombel $rombel, PeriodeNilai $periode, array $items): array
    {
        return DB::transaction(function () use ($rombel, $periode, $items) {
            // Urutan kunci tetap: rombel → periode → paket, sama dengan alur kirim/kunci.
            Rombel::whereKey($rombel->id_rombel)->lockForUpdate()->first();
            $periode = PeriodeNilai::whereKey($periode->id_periode)->lockForUpdate()->firstOrFail();
            PaketNilai::where('id_rombel', $rombel->id_rombel)->where('id_periode', $periode->id_periode)->lockForUpdate()->get();

            $this->periksa($rombel, $periode, $items);

            $simpan = 0;
            $hapus = 0;
            $kelas = [];
            foreach ($items as $item) {
                $kunci = ['id_tp' => $item['id_tp'], 'id_siswa_rombel' => $item['id_siswa_rombel']];
                if ($item['nilai'] === null) {
                    $hapus += NilaiTp::where($kunci)->delete();

                    continue;
                }
                NilaiTp::updateOrCreate($kunci, ['nilai' => $item['nilai'], 'id_periode' => $periode->id_periode, 'diubah_oleh' => Auth::id()]);
                $simpan++;
            }

            foreach (SiswaRombel::with('riwayatKelas:id_riwayat_kelas,id_kelas')->whereKey(array_column($items, 'id_siswa_rombel'))->get() as $p) {
                $kelas[$p->riwayatKelas->id_kelas] = true;
            }
            foreach (array_keys($kelas) as $idKelas) {
                PaketNilai::firstOrCreate(['id_rombel' => $rombel->id_rombel, 'id_periode' => $periode->id_periode, 'id_kelas' => $idKelas], ['status' => 'draf']);
            }

            $this->audit->catat('simpan_nilai', 'rombel', $rombel->id_rombel, null, [
                'id_periode' => $periode->id_periode, 'tersimpan' => $simpan, 'dihapus' => $hapus,
                'sel' => array_map(fn ($i) => [$i['id_siswa_rombel'], $i['id_tp'], $i['nilai']], array_slice($items, 0, 200)),
            ]);

            return ['tersimpan' => $simpan, 'dihapus' => $hapus];
        });
    }

    public function pastikanPeriodeRombel(Rombel $rombel, PeriodeNilai $periode): void
    {
        if ((int) $periode->id_semester !== (int) $rombel->id_semester) {
            throw new AturanBisnisException('Periode tidak berada di semester rombel ini.', 'VALIDATION_ERROR', 422, ['id_periode' => ['Semester tidak sesuai.']]);
        }
    }

    private function periodeBisaInput(PeriodeNilai $periode, ?PaketNilai $paket): bool
    {
        if ($periode->status === 'dikunci') {
            return false;
        }

        return $periode->terbukaUntukInput() || ($paket?->koreksi === true);
    }

    /** null = boleh; string = alasan ditolak. */
    private function selBisa(Tp $tp, ?NilaiTp $ada, PeriodeNilai $periode): ?string
    {
        if ($tp->hanya_sts && $periode->jenis === 'SAS') {
            return "TP ke-{$tp->urutan} hanya dinilai pada periode STS.";
        }
        if ($ada && (int) $ada->id_periode !== (int) $periode->id_periode) {
            return "Nilai TP ke-{$tp->urutan} diisi pada periode lain dan tidak dapat diubah di periode ini.";
        }

        return null;
    }

    /** Dipakai rekap guru: semua peserta rombel (tanpa filter periode) dan ringkasan nilainya. */
    public function rekapRombel(Rombel $rombel, ?PeriodeNilai $sts): Collection
    {
        $tp = $rombel->tp()->get();
        $peserta = $this->nilai->pesertaPeriode($rombel);
        $nilai = $this->nilai->nilaiPeserta($peserta->pluck('id_siswa_rombel'));

        return $peserta->map(fn (SiswaRombel $p) => [
            'siswa' => $p->riwayatKelas->siswa->only(['id_siswa', 'nis', 'nama']),
            'kelas' => $p->riwayatKelas->kelas->nama,
            'nilai_tp' => $tp->mapWithKeys(fn (Tp $t) => [$t->urutan => $nilai->get($p->id_siswa_rombel)?->get($t->id_tp)?->nilai]),
        ] + NilaiService::hitung($tp, $nilai->get($p->id_siswa_rombel, collect()), $sts?->id_periode));
    }
}
