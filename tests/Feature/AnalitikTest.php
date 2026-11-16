<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\SkenarioPenilaian;
use Tests\TestCase;

class AnalitikTest extends TestCase
{
    use RefreshDatabase, SkenarioPenilaian;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siapkanSkenario();
        [$t1, $t2, $t3] = $this->buatTp(3);
        $this->sebagai($this->userGuru, 'GURU');
        // Siswa 0: 90 → 80 → 70 (turun dua kali berturut-turut, tetap ≥ KKTP kecuali 70)
        // Siswa 1: 60 → 65 → 80
        $this->isi($this->sts, [
            [$this->peserta[0], $t1, 90], [$this->peserta[0], $t2, 80], [$this->peserta[0], $t3, 70],
            [$this->peserta[1], $t1, 60], [$this->peserta[1], $t2, 65], [$this->peserta[1], $t3, 80],
        ])->assertOk();
    }

    private function q(string $cakupan): string
    {
        return "id_periode={$this->sts->id_periode}&{$cakupan}";
    }

    public function test_sebaran_nilai_kelas(): void
    {
        $this->sebagai($this->userKurikulum)->getJson('/api/analitik/sebaran?'.$this->q("id_kelas={$this->kelasXA->id_kelas}"))
            ->assertOk()
            ->assertJsonPath('data.mapel.0.n', 2)
            ->assertJsonPath('data.mapel.0.rerata', 74.17)       // (80 + 68.33) / 2
            ->assertJsonPath('data.mapel.0.min', 68.33)
            ->assertJsonPath('data.mapel.0.maks', 80)
            ->assertJsonPath('data.mapel.0.simpangan_baku', 5.84); // |80 - 68.33| / 2
    }

    public function test_ketuntasan_per_mapel_dan_tp(): void
    {
        $res = $this->sebagai($this->userGuru, 'GURU')->getJson('/api/analitik/ketuntasan?'.$this->q("id_rombel={$this->rombel->id_rombel}"))->assertOk();

        $this->assertEquals(50, $res->json('data.mapel.0.persen_tuntas'));      // 80 tuntas, 68.33 tidak (KKTP 75)
        $this->assertEquals([50, 50, 50], array_column($res->json('data.mapel.0.per_tp'), 'persen_tuntas'));
    }

    public function test_early_warning_di_bawah_kktp_dan_turun_berturut(): void
    {
        $data = $this->sebagai($this->userWali, 'WALI_KELAS')->getJson('/api/analitik/early-warning?'.$this->q("id_kelas={$this->kelasXA->id_kelas}"))
            ->assertOk()->assertJsonPath('data.jumlah', 2)->json('data.siswa');

        $turun = collect($data)->firstWhere('siswa.id_siswa', $this->peserta[0]->riwayatKelas->id_siswa);
        $this->assertContains('Nilai turun pada dua asesmen berturut-turut', $turun['alasan']);
        $this->assertSame('tinggi', $turun['tingkat']); // turun + ada TP < KKTP
        $rendah = collect($data)->firstWhere('siswa.id_siswa', $this->peserta[1]->riwayatKelas->id_siswa);
        $this->assertSame(['2 TP di bawah KKTP'], $rendah['alasan']);
    }

    public function test_akses_analitik_dibatasi_peran(): void
    {
        $waliLain = $this->buatUser(['WALI_KELAS']);
        $this->guru($waliLain);
        $this->sebagai($waliLain, 'WALI_KELAS')->getJson('/api/analitik/sebaran?'.$this->q("id_kelas={$this->kelasXA->id_kelas}"))->assertForbidden();

        $guruLain = $this->buatUser(['GURU']);
        $this->guru($guruLain);
        $this->sebagai($guruLain, 'GURU')->getJson('/api/analitik/sebaran?'.$this->q("id_rombel={$this->rombel->id_rombel}"))->assertForbidden();

        $siswa = $this->buatUser(['SISWA']);
        $this->peserta[0]->riwayatKelas->siswa->forceFill(['id_user' => $siswa->id_user])->save();
        $this->sebagai($siswa)->getJson('/api/analitik/siswa/'.$this->peserta[1]->riwayatKelas->id_siswa.'/tren')->assertForbidden();
        $this->getJson('/api/portal/analitik/tren')->assertOk()->assertJsonPath('data.tren.0.rata_sts', 80);
    }
}
