<?php

namespace App\Services;

use App\Exceptions\AturanBisnisException;
use App\Models\Kelas;
use App\Models\PaketNilai;
use App\Models\PeriodeNilai;
use App\Models\PeriodeTingkat;
use App\Models\Semester;
use App\Models\SiswaRombel;
use App\Models\VerifikasiNilai;
use App\Models\WaliKelas;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Periode STS/SAS per semester: jadwal, tingkat peserta, ringkasan kelas, dan penguncian.
 * Penguncian TIDAK menerbitkan rapor (itu modul rapor).
 */
class PeriodeService
{
    public const SIAP_DIKUNCI = ['disetujui', 'dikunci'];

    public function __construct(
        private AuditService $audit,
        private NotifikasiService $notif,
        private NilaiService $nilai,
    ) {}

    /** Setiap semester selalu punya periode STS & SAS (status awal belum_diatur). */
    public function pastikanAda(Semester $semester): Collection
    {
        foreach (PeriodeNilai::JENIS as $jenis) {
            $p = PeriodeNilai::firstOrCreate(['id_semester' => $semester->id_semester, 'jenis' => $jenis], ['status' => 'belum_diatur']);
            if ($p->wasRecentlyCreated) {
                // Default: kelas XII hanya ikut periode semester ganjil.
                $tingkat = $semester->nama === 'GENAP' ? ['X', 'XI'] : Kelas::TINGKAT;
                foreach ($tingkat as $t) {
                    PeriodeTingkat::create(['id_periode' => $p->id_periode, 'tingkat' => $t]);
                }
            }
        }

        return PeriodeNilai::where('id_semester', $semester->id_semester)->urutJenis()->get();
    }

    /** @param list<string>|null $tingkat */
    public function atur(PeriodeNilai $periode, string $buka, string $tutup, ?array $tingkat): PeriodeNilai
    {
        $this->pastikanBelumDikunci($periode);

        return DB::transaction(function () use ($periode, $buka, $tutup, $tingkat) {
            $sebelum = $periode->only(['tgl_buka', 'tgl_tutup', 'status']) + ['tingkat' => $periode->tingkat];
            $baruDibuka = $periode->status === 'belum_diatur';

            $periode->update([
                'tgl_buka' => Carbon::parse($buka)->setTimezone(config('app.timezone')),
                'tgl_tutup' => Carbon::parse($tutup)->setTimezone(config('app.timezone')),
                'status' => 'dibuka',
            ]);

            if ($tingkat !== null) {
                $dilepas = array_diff($periode->tingkat, $tingkat);
                if ($dilepas !== [] && $this->adaPaketTerkirim($periode, $dilepas)) {
                    throw AturanBisnisException::terkunci('Tingkat yang sudah memiliki nilai terkirim tidak dapat dikeluarkan dari periode.');
                }
                PeriodeTingkat::where('id_periode', $periode->id_periode)->delete();
                foreach (array_unique($tingkat) as $t) {
                    PeriodeTingkat::create(['id_periode' => $periode->id_periode, 'tingkat' => $t]);
                }
            }
            $periode = $periode->fresh();

            $this->audit->catat('atur_periode', 'periode_nilai', $periode->id_periode, $sebelum,
                $periode->only(['tgl_buka', 'tgl_tutup', 'status']) + ['tingkat' => $periode->tingkat]);

            $this->notif->kirim($this->notif->userGuruSemester($periode->id_semester), $baruDibuka ? 'periode_dibuka' : 'periode_diubah',
                "Periode {$periode->jenis} ".($baruDibuka ? 'dibuka' : 'diubah'),
                "Input nilai {$periode->jenis} dapat dilakukan {$periode->tgl_buka->format('d/m/Y H:i')} s.d. {$periode->tgl_tutup->format('d/m/Y H:i')}.",
                ['id_periode' => $periode->id_periode]);

            return $periode;
        });
    }

    /** Tutup sekarang: input berhenti, verifikasi & penguncian tetap berjalan. */
    public function tutup(PeriodeNilai $periode): PeriodeNilai
    {
        $this->pastikanBelumDikunci($periode);
        if ($periode->status_efektif !== 'dibuka') {
            throw new AturanBisnisException('Periode tidak sedang dibuka.');
        }

        return DB::transaction(function () use ($periode) {
            $sebelum = ['tgl_tutup' => $periode->tgl_tutup];
            $periode->update(['tgl_tutup' => now()->subSecond()]);
            $this->audit->catat('tutup_periode', 'periode_nilai', $periode->id_periode, $sebelum, ['tgl_tutup' => $periode->tgl_tutup]);
            $this->notif->kirim($this->notif->userGuruSemester($periode->id_semester), 'periode_ditutup',
                "Periode {$periode->jenis} ditutup", 'Input nilai sudah ditutup oleh Kurikulum.', ['id_periode' => $periode->id_periode]);

            return $periode->fresh();
        });
    }

    /**
     * Buat paket draf untuk setiap pasangan rombel × kelas peserta periode (idempoten).
     */
    public function sinkronkanPaket(PeriodeNilai $periode): void
    {
        $pasangan = SiswaRombel::query()
            ->join('rombel as r', 'r.id_rombel', '=', 'siswa_rombel.id_rombel')
            ->join('riwayat_kelas as rk', 'rk.id_riwayat_kelas', '=', 'siswa_rombel.id_riwayat_kelas')
            ->join('kelas as k', 'k.id_kelas', '=', 'rk.id_kelas')
            ->where('r.id_semester', $periode->id_semester)
            ->whereIn('k.tingkat', $periode->tingkat ?: ['-'])
            ->select('r.id_rombel', 'rk.id_kelas')->distinct()->get();

        foreach ($pasangan as $p) {
            PaketNilai::firstOrCreate(['id_periode' => $periode->id_periode, 'id_rombel' => $p->id_rombel, 'id_kelas' => $p->id_kelas], ['status' => 'draf']);
        }
    }

    /** Ringkasan per kelas untuk layar "Periode & Penguncian". */
    public function ringkasan(PeriodeNilai $periode): array
    {
        $this->sinkronkanPaket($periode);
        $semester = $periode->semester;

        $kelas = Kelas::where('id_tahun_ajaran', $semester->id_tahun_ajaran)->whereIn('tingkat', $periode->tingkat ?: ['-'])
            ->orderByRaw("CASE tingkat WHEN 'X' THEN 1 WHEN 'XI' THEN 2 ELSE 3 END")->orderBy('nama')->get();
        $wali = WaliKelas::with('guru:id_guru,nama')->where('id_semester', $semester->id_semester)->get()->keyBy('id_kelas');
        $paket = PaketNilai::with(['rombel.mapel:id_mapel,nama', 'rombel.guru:id_guru,nama'])->where('id_periode', $periode->id_periode)->get()->groupBy('id_kelas');

        return $kelas->map(function (Kelas $k) use ($wali, $paket) {
            $p = $paket->get($k->id_kelas, collect());

            return [
                'id_kelas' => $k->id_kelas,
                'kelas' => $k->nama,
                'tingkat' => $k->tingkat,
                'wali_kelas' => $wali->get($k->id_kelas)?->guru?->nama,
                'mapel_siap' => $p->whereIn('status', self::SIAP_DIKUNCI)->where('lengkap', true)->count(),
                'mapel_total' => $p->count(),
                'status' => $this->statusKelas($p),
                'paket' => $p->map(fn (PaketNilai $x) => [
                    'id_paket_nilai' => $x->id_paket_nilai,
                    'mapel' => $x->rombel->mapel->nama,
                    'rombel' => $x->rombel->nama,
                    'guru' => $x->rombel->guru->pluck('nama')->values(),
                    'status' => $x->status,
                    'lengkap' => $x->lengkap,
                    'koreksi' => $x->koreksi,
                ])->sortBy('mapel')->values(),
            ];
        })->values()->all();
    }

    public function statusKelas(Collection $paket): string
    {
        return match (true) {
            $paket->isEmpty() => 'belum_ada_mapel',
            $paket->every(fn ($p) => $p->status === 'dikunci') => 'dikunci',
            $paket->contains(fn ($p) => $p->koreksi) => 'dibuka_koreksi',
            $paket->contains(fn ($p) => $p->status === 'perlu_revisi') => 'perlu_revisi',
            $paket->every(fn ($p) => in_array($p->status, self::SIAP_DIKUNCI, true)) => $paket->every('lengkap') ? 'siap_dikunci' : 'nilai_belum_lengkap',
            $paket->contains(fn ($p) => $p->status === 'terkirim') => 'menunggu_verifikasi',
            default => 'pengisian',
        };
    }

    /** Kunci seluruh mapel satu kelas. Semua paket wajib sudah disetujui wali kelas. */
    public function kunciKelas(PeriodeNilai $periode, Kelas $kelas): int
    {
        if (! $periode->ikutTingkat($kelas->tingkat)) {
            throw new AturanBisnisException('Tingkat kelas tidak ikut periode ini.');
        }
        $this->sinkronkanPaket($periode);

        return DB::transaction(function () use ($periode, $kelas) {
            $paket = PaketNilai::with('rombel:id_rombel,nama')->where('id_periode', $periode->id_periode)
                ->where('id_kelas', $kelas->id_kelas)->lockForUpdate()->get();

            if ($paket->isEmpty()) {
                throw new AturanBisnisException('Belum ada mapel pada kelas ini.');
            }
            $belum = $paket->reject(fn ($p) => in_array($p->status, self::SIAP_DIKUNCI, true));
            if ($belum->isNotEmpty()) {
                throw new AturanBisnisException('Masih ada mapel yang belum disetujui wali kelas: '.$belum->pluck('rombel.nama')->implode(', ').'.', 'PAKET_BELUM_SIAP');
            }
            $tidakLengkap = $paket->filter(fn (PaketNilai $p) => ! $this->nilai->kelengkapan($p->rombel, $periode, $kelas->id_kelas)['lengkap']);
            if ($tidakLengkap->isNotEmpty()) {
                throw new AturanBisnisException('Nilai belum lengkap: '.$tidakLengkap->pluck('rombel.nama')->implode(', ').'.', 'PAKET_BELUM_LENGKAP');
            }

            $now = now();
            foreach ($paket->where('status', 'disetujui') as $p) {
                $p->update(['status' => 'dikunci', 'koreksi' => false, 'dikunci_oleh' => Auth::id(), 'waktu_kunci' => $now]);
                VerifikasiNilai::create(['id_paket_nilai' => $p->id_paket_nilai, 'id_user' => Auth::id(), 'aksi' => 'kunci']);
            }

            $this->audit->catat('kunci_nilai_kelas', 'periode_nilai', $periode->id_periode, null, ['id_kelas' => $kelas->id_kelas, 'jumlah_mapel' => $paket->count()]);
            $this->kunciPeriodeBilaSelesai($periode);

            return $paket->count();
        });
    }

    /** @return list<string> nama kelas yang berhasil dikunci */
    public function kunciSemuaSiap(PeriodeNilai $periode): array
    {
        $dikunci = [];
        foreach ($this->ringkasan($periode) as $baris) {
            if ($baris['status'] === 'siap_dikunci') {
                $this->kunciKelas($periode, Kelas::findOrFail($baris['id_kelas']));
                $dikunci[] = $baris['kelas'];
            }
        }

        return $dikunci;
    }

    /** Buka koreksi satu paket terkunci; wajib beralasan, nilai lama tetap tercatat di riwayat & rapor terarsip. */
    public function bukaKoreksi(PaketNilai $paket, string $alasan): PaketNilai
    {
        return DB::transaction(function () use ($paket, $alasan) {
            $paket = PaketNilai::with(['rombel', 'periode'])->lockForUpdate()->findOrFail($paket->id_paket_nilai);
            if ($paket->status !== 'dikunci') {
                throw new AturanBisnisException('Hanya nilai yang sudah dikunci yang dapat dibuka koreksinya.');
            }

            $paket->update(['status' => 'perlu_revisi', 'koreksi' => true, 'alasan_koreksi' => $alasan]);
            VerifikasiNilai::create(['id_paket_nilai' => $paket->id_paket_nilai, 'id_user' => Auth::id(), 'aksi' => 'buka_koreksi', 'catatan' => $alasan]);
            if ($paket->periode->status === 'dikunci') {
                $paket->periode->update(['status' => 'dibuka']);
            }

            $this->audit->catat('buka_koreksi', 'paket_nilai', $paket->id_paket_nilai, ['status' => 'dikunci'], ['status' => 'perlu_revisi'], $alasan);
            $this->notif->kirim(
                [...$this->notif->userGuruRombel($paket->rombel), ...$this->notif->userWaliKelas($paket->id_kelas, $paket->rombel->id_semester)],
                'koreksi_dibuka', 'Koreksi nilai dibuka', "Kurikulum membuka koreksi {$paket->rombel->nama}: {$alasan}",
                ['id_paket_nilai' => $paket->id_paket_nilai],
            );

            return $paket->fresh();
        });
    }

    public function pastikanBelumDikunci(PeriodeNilai $periode): void
    {
        if ($periode->status === 'dikunci') {
            throw AturanBisnisException::terkunci('Periode sudah dikunci.');
        }
    }

    private function kunciPeriodeBilaSelesai(PeriodeNilai $periode): void
    {
        $sisa = PaketNilai::where('id_periode', $periode->id_periode)->where('status', '!=', 'dikunci')->exists();
        if (! $sisa) {
            $periode->update(['status' => 'dikunci']);
            $this->audit->catat('kunci_periode', 'periode_nilai', $periode->id_periode, null, ['status' => 'dikunci']);
        }
    }

    private function adaPaketTerkirim(PeriodeNilai $periode, array $tingkat): bool
    {
        return PaketNilai::where('id_periode', $periode->id_periode)->whereNotIn('status', PaketNilai::BISA_DIEDIT)
            ->whereHas('kelas', fn ($q) => $q->whereIn('tingkat', $tingkat))->exists();
    }
}
