<?php

namespace Tests\Support;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\PeriodeNilai;
use App\Models\PeriodeTingkat;
use App\Models\Rombel;
use App\Models\SiswaRombel;
use App\Models\Tp;
use App\Models\User;
use App\Models\WaliKelas;
use Illuminate\Testing\TestResponse;

/**
 * Skenario kecil: rombel Matematika X (guru Budi), kelas X-A dengan 2 siswa,
 * wali kelas X-A (Sari), periode STS dibuka & SAS belum diatur.
 */
trait SkenarioPenilaian
{
    use SiakadFixtures;

    protected User $userGuru;

    protected Guru $guruMapel;

    protected User $userWali;

    protected User $userKurikulum;

    protected Kelas $kelasXA;

    protected Rombel $rombel;

    /** @var list<SiswaRombel> */
    protected array $peserta = [];

    protected PeriodeNilai $sts;

    protected PeriodeNilai $sas;

    protected function siapkanSkenario(int $jumlahSiswa = 2): void
    {
        $this->seedReferensi();
        $semester = $this->semesterAktif();

        $this->userGuru = $this->buatUser(['GURU'], ['username' => 'budi']);
        $this->guruMapel = $this->guru($this->userGuru, ['nama' => 'Budi']);
        $this->userWali = $this->buatUser(['GURU', 'WALI_KELAS'], ['username' => 'sari']);
        $guruWali = $this->guru($this->userWali, ['nama' => 'Sari']);
        $this->userKurikulum = $this->buatUser(['KURIKULUM']);

        $this->kelasXA = $this->kelas('X-A');
        WaliKelas::create(['id_kelas' => $this->kelasXA->id_kelas, 'id_guru' => $guruWali->id_guru, 'id_semester' => $semester->id_semester]);

        $mapel = Mapel::create(['kode' => 'MTK', 'nama' => 'Matematika']);
        $this->rombel = Rombel::create(['id_semester' => $semester->id_semester, 'id_mapel' => $mapel->id_mapel, 'nama' => 'Matematika X']);
        $this->rombel->guru()->attach($this->guruMapel->id_guru);

        $this->peserta = [];
        for ($i = 0; $i < $jumlahSiswa; $i++) {
            $anggota = $this->anggota($this->siswa(), $this->kelasXA);
            $this->peserta[] = SiswaRombel::create(['id_rombel' => $this->rombel->id_rombel, 'id_riwayat_kelas' => $anggota->id_riwayat_kelas]);
        }

        $this->sts = $this->periode('STS', 'dibuka');
        $this->sas = $this->periode('SAS', 'belum_diatur');
    }

    protected function periode(string $jenis, string $status): PeriodeNilai
    {
        $p = PeriodeNilai::create([
            'id_semester' => $this->semesterAktif()->id_semester, 'jenis' => $jenis, 'status' => $status,
            'tgl_buka' => $status === 'belum_diatur' ? null : now()->subDay(),
            'tgl_tutup' => $status === 'belum_diatur' ? null : now()->addDays(7),
        ]);
        foreach (['X', 'XI', 'XII'] as $t) {
            PeriodeTingkat::create(['id_periode' => $p->id_periode, 'tingkat' => $t]);
        }

        return $p->fresh();
    }

    protected function bukaSas(): void
    {
        $this->sas->update(['status' => 'dibuka', 'tgl_buka' => now()->subDay(), 'tgl_tutup' => now()->addDays(7)]);
        $this->sas->refresh();
    }

    /** @return list<Tp> */
    protected function buatTp(int $n, ?Rombel $rombel = null): array
    {
        $rombel ??= $this->rombel;
        $tp = [];
        for ($i = 1; $i <= $n; $i++) {
            $tp[] = Tp::create(['id_rombel' => $rombel->id_rombel, 'urutan' => $i, 'deskripsi' => "Tujuan pembelajaran {$i}"]);
        }

        return $tp;
    }

    protected function isi(PeriodeNilai $periode, array $sel): TestResponse
    {
        return $this->putJson("/api/guru/rombel/{$this->rombel->id_rombel}/nilai", [
            'id_periode' => $periode->id_periode,
            'items' => array_map(fn ($s) => ['id_siswa_rombel' => $s[0]->id_siswa_rombel, 'id_tp' => $s[1]->id_tp, 'nilai' => $s[2]], $sel),
        ]);
    }
}
