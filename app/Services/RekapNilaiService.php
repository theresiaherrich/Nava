<?php

namespace App\Services;

use App\Models\NilaiTp;
use App\Models\PaketNilai;
use App\Models\PeriodeNilai;
use App\Models\Rombel;
use App\Models\SiswaRombel;
use App\Models\User;
use App\Models\WaliKelas;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class RekapNilaiService
{
    public function query(User $user, PeriodeNilai $periode, array $filters): Builder
    {
        $query = SiswaRombel::query()
            ->whereHas('rombel', fn ($q) => $q->where('semester_id', $periode->semester_id))
            ->whereHas('siswaKelas', fn ($q) => $q->where('semester_id', $periode->semester_id)
                ->whereHas('kelas', fn ($k) => $k->where('semester_id', $periode->semester_id)->where('tingkat', $periode->tingkat)))
            ->when($filters['kelas_id'] ?? null, fn ($q, $id) => $q->whereHas('siswaKelas', fn ($s) => $s->where('kelas_id', $id)))
            ->when($filters['rombel_id'] ?? null, fn ($q, $id) => $q->where('rombel_id', $id));

        if (! $user->hasAnyRole(['admin', 'kurikulum'])) {
            $guruId = PenilaianAccess::guruId($user);
            $guru = $user->hasAnyRole(['guru']);
            $wali = $user->hasAnyRole(['wali_kelas']);
            $kelasIds = $wali && $guruId ? WaliKelas::where('guru_id', $guruId)->pluck('kelas_id') : collect();
            $query->where(function ($q) use ($guruId, $guru, $kelasIds) {
                $q->whereRaw('1 = 0');
                if ($guru && $guruId !== null) {
                    $q->orWhereHas('rombel.gurus', fn ($g) => $g->where('guru.id', $guruId));
                }
                $q->orWhereHas('siswaKelas', fn ($s) => $s->whereIn('kelas_id', $kelasIds));
            });
        }

        return $query->with(['siswaKelas.siswa', 'siswaKelas.kelas', 'rombel.mapel',
            'rombel.tp' => fn ($q) => $q->when($periode->jenis === 'STS', fn ($tp) => $tp->where('masuk_sts', true))])
            ->orderBy('siswa_rombel.id');
    }

    public function pastikanAksesRombel(User $user, Rombel $rombel, PeriodeNilai $periode): void
    {
        $guruId = PenilaianAccess::guruId($user);
        $waliIds = $user->hasAnyRole(['wali_kelas']) && $guruId
            ? WaliKelas::where('guru_id', $guruId)->pluck('kelas_id') : collect();
        $allowed = $user->hasAnyRole(['admin', 'kurikulum'])
            || ($user->hasAnyRole(['guru']) && $rombel->dapatDiampuOleh($guruId))
            || $rombel->peserta()->whereHas('siswaKelas', fn ($s) => $s->whereIn('kelas_id', $waliIds))->exists();
        abort_unless($allowed, 403, 'Anda tidak memiliki akses rekap rombel ini.');
        abort_unless((int) $rombel->semester_id === (int) $periode->semester_id, 422, 'Semester rombel tidak sesuai periode.');
        // Rombel tidak memiliki kolom tingkat; tingkat disaring pada kelas asal peserta.
    }

    public function rows(Collection $peserta, PeriodeNilai $periode): Collection
    {
        $tpIds = $peserta->flatMap(fn ($p) => $p->rombel->tp->pluck('id'))->unique();
        $nilai = NilaiTp::whereIn('siswa_rombel_id', $peserta->pluck('id'))->whereIn('tp_id', $tpIds)
            ->get()->keyBy(fn ($n) => $n->siswa_rombel_id.':'.$n->tp_id);
        $paket = PaketNilai::where('periode_id', $periode->id)
            ->whereIn('rombel_id', $peserta->pluck('rombel_id')->unique())
            ->whereIn('kelas_id', $peserta->map(fn ($p) => $p->siswaKelas->kelas_id)->unique())
            ->get()->keyBy(fn ($p) => $p->rombel_id.':'.$p->kelas_id);

        return $peserta->map(function ($p) use ($nilai, $paket) {
            $sk = $p->siswaKelas;
            $scores = $p->rombel->tp->map(fn ($tp) => [
                'tp_id' => $tp->id, 'nomor_urut' => $tp->nomor_urut,
                'skor' => $nilai->get($p->id.':'.$tp->id)?->skor,
            ])->values();
            $terisi = $scores->whereNotNull('skor')->count();

            return [
                'siswa_rombel_id' => $p->id,
                'siswa' => ['id' => $sk->siswa->id, 'nis' => $sk->siswa->nis, 'nama' => $sk->siswa->nama],
                'kelas' => ['id' => $sk->kelas->id, 'nama' => $sk->kelas->nama],
                'rombel' => ['id' => $p->rombel->id, 'nama' => $p->rombel->nama],
                'mapel' => ['id' => $p->rombel->mapel->id, 'nama' => $p->rombel->mapel->nama],
                'nilai_tp' => $scores->all(), 'jumlah_tp' => $scores->count(), 'jumlah_tp_terisi' => $terisi,
                'lengkap' => $scores->isNotEmpty() && $scores->count() === $terisi,
                'status_paket' => $paket->get($p->rombel_id.':'.$sk->kelas_id)?->status,
            ] + $this->nilaiAkhir($scores);
        })->values();
    }

    protected function nilaiAkhir(Collection $scores): array
    {
        if ($scores->isEmpty()) {
            return ['nilai_akhir' => null, 'alasan_nilai_akhir' => 'tidak_ada_tp'];
        }
        if ($scores->contains(fn ($score) => $score['skor'] === null)) {
            return ['nilai_akhir' => null, 'alasan_nilai_akhir' => 'nilai_belum_lengkap'];
        }

        // Skor database memiliki dua desimal. Hitung dalam satuan 1/100 agar
        // penjumlahan tidak terkena galat floating point. Hasil disajikan enam
        // desimal (pecahan berulang dipotong), bukan pembulatan nilai rapor.
        $totalPerseratus = $scores->sum(function ($score) {
            [$utuh, $desimal] = explode('.', $score['skor']);

            return (int) $utuh * 100 + (int) $desimal;
        });
        $hasil = intdiv($totalPerseratus * 10000, $scores->count());
        $nilai = intdiv($hasil, 1000000).'.'.str_pad((string) ($hasil % 1000000), 6, '0', STR_PAD_LEFT);

        return ['nilai_akhir' => $nilai, 'alasan_nilai_akhir' => null];
    }
}
