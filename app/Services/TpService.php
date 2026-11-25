<?php

namespace App\Services;

use App\Exceptions\AturanBisnisException;
use App\Models\NilaiTp;
use App\Models\PeriodeNilai;
use App\Models\Rombel;
use App\Models\Tp;
use Illuminate\Support\Facades\DB;

/**
 * Tujuan Pembelajaran per rombel (pengajaran per semester).
 *  - Maks. 10 TP, urutan selalu rapat 1..n (dinomori ulang setelah hapus/geser).
 *  - TP dengan urutan terbesar = TP SAS; menggeser urutan mengubah TP SAS.
 *  - Struktur terkunci bila periode SAS semester sudah dikunci.
 */
class TpService
{
    public function __construct(private AuditService $audit) {}

    public function tambah(Rombel $rombel, array $data): Tp
    {
        return DB::transaction(function () use ($rombel, $data) {
            $this->kunciRombel($rombel);
            $this->pastikanStrukturTerbuka($rombel);

            $jumlah = Tp::where('id_rombel', $rombel->id_rombel)->count();
            if ($jumlah >= Tp::MAKS_PER_ROMBEL) {
                throw new AturanBisnisException('Maksimal '.Tp::MAKS_PER_ROMBEL.' TP per rombel dalam satu semester.', 'TP_LIMIT');
            }

            $tp = Tp::create([
                'id_rombel' => $rombel->id_rombel,
                'urutan' => $jumlah + 1,
                'kode' => $data['kode'] ?? null,
                'deskripsi' => $data['deskripsi'],
                'hanya_sts' => $data['hanya_sts'] ?? false,
            ]);
            $this->audit->catat('tambah_tp', 'tp', $tp->id_tp, null, $tp->only(['id_rombel', 'urutan', 'kode', 'deskripsi', 'hanya_sts']));

            return $tp;
        });
    }

    public function ubah(Tp $tp, array $data): Tp
    {
        return DB::transaction(function () use ($tp, $data) {
            $this->kunciRombel($tp->rombel);
            $this->pastikanStrukturTerbuka($tp->rombel);

            if (array_key_exists('hanya_sts', $data) && (bool) $data['hanya_sts'] !== $tp->hanya_sts && $this->punyaNilai($tp)) {
                throw AturanBisnisException::terkunci('Mode "STS saja" tidak dapat diubah karena TP sudah memiliki nilai.');
            }
            if ($this->nilaiTerkunci($tp)) {
                throw AturanBisnisException::terkunci('TP tidak dapat diubah karena nilainya sudah dikirim atau dikunci.');
            }

            $sebelum = $tp->only(array_keys($data));
            $tp->update($data);
            $this->audit->catat('ubah_tp', 'tp', $tp->id_tp, $sebelum, $data);

            return $tp->fresh();
        });
    }

    public function hapus(Tp $tp): void
    {
        DB::transaction(function () use ($tp) {
            $this->kunciRombel($tp->rombel);
            $this->pastikanStrukturTerbuka($tp->rombel);

            if ($this->punyaNilai($tp)) {
                throw AturanBisnisException::terkunci('TP yang sudah memiliki nilai tidak dapat dihapus.');
            }

            $tp->delete();
            $this->rapatkan($tp->id_rombel);
            $this->audit->catat('hapus_tp', 'tp', $tp->id_tp, $tp->only(['id_rombel', 'urutan', 'deskripsi']));
        });
    }

    /** @param list<int> $idTpUrut seluruh id_tp rombel dalam urutan baru */
    public function urutkan(Rombel $rombel, array $idTpUrut): void
    {
        DB::transaction(function () use ($rombel, $idTpUrut) {
            $this->kunciRombel($rombel);
            $this->pastikanStrukturTerbuka($rombel);

            $ada = Tp::where('id_rombel', $rombel->id_rombel)->orderBy('urutan')->pluck('id_tp')->all();
            if (count($idTpUrut) !== count($ada) || array_diff($ada, $idTpUrut) !== []) {
                throw new AturanBisnisException('Daftar urutan harus memuat seluruh TP rombel tepat satu kali.', 'VALIDATION_ERROR', 422, ['id_tp' => ['Tidak lengkap atau berisi TP rombel lain.']]);
            }
            $terakhirLama = end($ada);
            if ($terakhirLama !== end($idTpUrut) && NilaiTp::where('id_tp', $terakhirLama)->whereNotNull('nilai')
                ->whereHas('periode', fn ($q) => $q->where('jenis', 'SAS'))->exists()) {
                throw AturanBisnisException::terkunci('TP terakhir (SAS) sudah dinilai pada periode SAS dan tidak dapat digeser.');
            }

            $this->tulisUrutan($idTpUrut);
            $this->audit->catat('urutkan_tp', 'rombel', $rombel->id_rombel, ['urutan' => $ada], ['urutan' => $idTpUrut]);
        });
    }

    /**
     * Salin TP ke rombel lain (mapel sama) sebagai baris baru di akhir daftar.
     *
     * @param  list<int>  $idTp
     * @return list<Tp>
     */
    public function salin(Rombel $tujuan, array $idTp): array
    {
        return DB::transaction(function () use ($tujuan, $idTp) {
            $this->kunciRombel($tujuan);
            $this->pastikanStrukturTerbuka($tujuan);

            $sumber = Tp::with('rombel:id_rombel,id_mapel')->whereKey($idTp)->orderBy('id_rombel')->orderBy('urutan')->get();
            if ($sumber->count() !== count(array_unique($idTp))) {
                throw new AturanBisnisException('Ada TP sumber yang tidak ditemukan.', 'VALIDATION_ERROR', 422, ['id_tp' => ['TP tidak ditemukan.']]);
            }
            if ($sumber->contains(fn (Tp $t) => (int) $t->rombel->id_mapel !== (int) $tujuan->id_mapel)) {
                throw new AturanBisnisException('TP hanya dapat disalin antar rombel dengan mata pelajaran yang sama.');
            }
            if ($sumber->contains(fn (Tp $t) => (int) $t->id_rombel === (int) $tujuan->id_rombel)) {
                throw new AturanBisnisException('Rombel tujuan sama dengan rombel sumber.');
            }

            $jumlah = Tp::where('id_rombel', $tujuan->id_rombel)->count();
            if ($jumlah + $sumber->count() > Tp::MAKS_PER_ROMBEL) {
                throw new AturanBisnisException('Penyalinan melebihi batas '.Tp::MAKS_PER_ROMBEL." TP (rombel tujuan sudah punya {$jumlah} TP).", 'TP_LIMIT');
            }

            $baru = [];
            foreach ($sumber->values() as $i => $s) {
                $baru[] = Tp::create([
                    'id_rombel' => $tujuan->id_rombel, 'urutan' => $jumlah + $i + 1,
                    'kode' => $s->kode, 'deskripsi' => $s->deskripsi, 'kktp' => $s->kktp, 'hanya_sts' => $s->hanya_sts,
                ]);
            }
            $this->audit->catat('salin_tp', 'rombel', $tujuan->id_rombel, null, ['dari_tp' => $idTp, 'jumlah' => count($baru)]);

            return $baru;
        });
    }

    /** KKTP diatur Kurikulum. null = kembali ke KKTP default mapel. */
    public function aturKktp(Tp $tp, ?float $kktp): Tp
    {
        return DB::transaction(function () use ($tp, $kktp) {
            if ($this->nilaiTerkunci($tp)) {
                throw AturanBisnisException::terkunci('KKTP tidak dapat diubah karena nilai TP ini sudah dikunci.');
            }
            $sebelum = ['kktp' => $tp->kktp];
            $tp->update(['kktp' => $kktp]);
            $this->audit->catat('atur_kktp', 'tp', $tp->id_tp, $sebelum, ['kktp' => $kktp]);

            return $tp->fresh();
        });
    }

    public function strukturTerkunci(Rombel $rombel): bool
    {
        return PeriodeNilai::where('id_semester', $rombel->id_semester)->where('jenis', 'SAS')->where('status', 'dikunci')->exists();
    }

    private function pastikanStrukturTerbuka(Rombel $rombel): void
    {
        if ($this->strukturTerkunci($rombel)) {
            throw AturanBisnisException::terkunci('Periode SAS semester ini sudah dikunci; daftar TP tidak dapat diubah.');
        }
    }

    private function punyaNilai(Tp $tp): bool
    {
        return NilaiTp::where('id_tp', $tp->id_tp)->exists();
    }

    /** Nilai TP sudah masuk paket yang dikirim/disetujui/dikunci. */
    private function nilaiTerkunci(Tp $tp): bool
    {
        return NilaiTp::where('nilai_tp.id_tp', $tp->id_tp)
            ->join('siswa_rombel as sr', 'sr.id_siswa_rombel', '=', 'nilai_tp.id_siswa_rombel')
            ->join('riwayat_kelas as rk', 'rk.id_riwayat_kelas', '=', 'sr.id_riwayat_kelas')
            ->join('paket_nilai as p', fn ($j) => $j->on('p.id_rombel', '=', 'sr.id_rombel')->on('p.id_periode', '=', 'nilai_tp.id_periode')->on('p.id_kelas', '=', 'rk.id_kelas'))
            ->whereNotIn('p.status', ['draf', 'perlu_revisi'])
            ->exists();
    }

    private function kunciRombel(Rombel $rombel): void
    {
        Rombel::whereKey($rombel->id_rombel)->lockForUpdate()->first();
    }

    private function rapatkan(int $idRombel): void
    {
        $this->tulisUrutan(Tp::where('id_rombel', $idRombel)->orderBy('urutan')->pluck('id_tp')->all());
    }

    /** Dua langkah agar unique (id_rombel, urutan) tidak bentrok saat menukar nomor. */
    private function tulisUrutan(array $idTpUrut): void
    {
        foreach ($idTpUrut as $i => $id) {
            Tp::whereKey($id)->update(['urutan' => 100 + $i]);
        }
        foreach ($idTpUrut as $i => $id) {
            Tp::whereKey($id)->update(['urutan' => $i + 1]);
        }
    }
}
