<?php

namespace App\Services;

use App\Models\Kelas;
use App\Models\PaketNilai;
use App\Models\PeriodeNilai;
use App\Models\RiwayatKelas;
use App\Models\Rombel;
use App\Models\SiswaRombel;
use Illuminate\Support\Collection;

/**
 * Daftar Kumpulan Nilai (DKN) satu kelas pada satu periode:
 * baris = siswa, kolom = mapel. STS memakai nilai_sts, SAS memakai nilai akhir semester.
 * Membaca data kerja saat ini (bukan snapshot rapor).
 */
class DknService
{
    public function __construct(private NilaiService $nilai) {}

    public function kelas(Kelas $kelas, PeriodeNilai $periode): array
    {
        $semester = $periode->id_semester;
        $anggota = RiwayatKelas::with('siswa:id_siswa,nis,nisn,nama')->where('id_kelas', $kelas->id_kelas)->where('id_semester', $semester)->get()
            ->sortBy(fn ($a) => mb_strtolower($a->siswa->nama))->values();

        $peserta = SiswaRombel::whereIn('id_riwayat_kelas', $anggota->pluck('id_riwayat_kelas'))->get();
        $rombel = Rombel::with(['mapel', 'tp', 'guru:id_guru,nama'])->whereKey($peserta->pluck('id_rombel')->unique())->get()
            ->sortBy(fn ($r) => [$r->mapel->urutan_rapor, $r->mapel->nama])->values();
        $nilai = $this->nilai->nilaiPeserta($peserta->pluck('id_siswa_rombel'));
        $paket = PaketNilai::where('id_periode', $periode->id_periode)->where('id_kelas', $kelas->id_kelas)->get()->keyBy('id_rombel');
        $idSts = $periode->jenis === 'STS' ? $periode->id_periode : PeriodeNilai::where('id_semester', $semester)->where('jenis', 'STS')->value('id_periode');
        $kolomNilai = $periode->jenis === 'STS' ? 'nilai_sts' : 'nilai_akhir';
        $pesertaKey = $peserta->keyBy(fn ($p) => $p->id_riwayat_kelas.':'.$p->id_rombel);

        $mapel = $rombel->map(fn (Rombel $r) => [
            'id_rombel' => $r->id_rombel,
            'id_mapel' => $r->id_mapel,
            'kode' => $r->mapel->kode,
            'mapel' => $r->mapel->nama,
            'guru' => $r->guru->pluck('nama')->values(),
            'status_paket' => $paket->get($r->id_rombel)?->status ?? 'draf',
            'kelengkapan' => $this->nilai->kelengkapan($r, $periode, $kelas->id_kelas),
        ])->values();

        $baris = $anggota->map(function (RiwayatKelas $a) use ($rombel, $pesertaKey, $nilai, $idSts, $kolomNilai) {
            $kolom = [];
            foreach ($rombel as $r) {
                $p = $pesertaKey->get($a->id_riwayat_kelas.':'.$r->id_rombel);
                $kolom[$r->id_rombel] = $p ? NilaiService::hitung($r->tp, $nilai->get($p->id_siswa_rombel, collect()), $idSts ? (int) $idSts : null)[$kolomNilai] : null;
            }
            $angka = collect($kolom)->filter(fn ($v) => $v !== null);

            return [
                'id_riwayat_kelas' => $a->id_riwayat_kelas,
                'siswa' => $a->siswa->only(['id_siswa', 'nis', 'nisn', 'nama']),
                'nilai' => $kolom,
                'jumlah' => $angka->isEmpty() ? null : round($angka->sum(), 2),
                'rata_rata' => $angka->isEmpty() ? null : round($angka->avg(), 2),
            ];
        });

        return [
            'kelas' => $kelas->only(['id_kelas', 'nama', 'tingkat']),
            'periode' => $periode->only(['id_periode', 'jenis', 'id_semester']),
            'jenis_nilai' => $kolomNilai,
            'mapel' => $mapel,
            'siswa' => $this->peringkat($baris)->values(),
            'semua_dikunci' => $mapel->isNotEmpty() && $mapel->every(fn ($m) => $m['status_paket'] === 'dikunci'),
        ];
    }

    /** Peringkat berdasarkan rata-rata (nilai sama → peringkat sama). */
    private function peringkat(Collection $baris): Collection
    {
        $urut = $baris->filter(fn ($b) => $b['rata_rata'] !== null)->sortByDesc('rata_rata')->values();
        $rank = [];
        $sebelum = null;
        foreach ($urut as $i => $b) {
            $rank[$b['id_riwayat_kelas']] = $b['rata_rata'] === $sebelum ? end($rank) : $i + 1;
            $sebelum = $b['rata_rata'];
        }

        return $baris->map(fn ($b) => $b + ['peringkat' => $rank[$b['id_riwayat_kelas']] ?? null]);
    }
}
