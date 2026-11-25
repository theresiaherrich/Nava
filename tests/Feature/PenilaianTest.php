<?php

namespace Tests\Feature;

use App\Models\Mapel;
use App\Models\Notifikasi;
use App\Models\PaketNilai;
use App\Models\Rombel;
use App\Models\Tp;
use App\Services\NilaiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\SkenarioPenilaian;
use Tests\TestCase;

class PenilaianTest extends TestCase
{
    use RefreshDatabase, SkenarioPenilaian;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siapkanSkenario();
    }

    // ------------------------------------------------------------------ TP

    public function test_batas_sepuluh_tp_per_rombel(): void
    {
        $this->sebagai($this->userGuru, 'GURU');
        $url = "/api/guru/rombel/{$this->rombel->id_rombel}/tp";

        foreach (range(1, 10) as $i) {
            $this->postJson($url, ['deskripsi' => "Tujuan ke-{$i}"])->assertCreated()->assertJsonPath('data.urutan', $i);
        }
        $this->postJson($url, ['deskripsi' => 'Tujuan ke-11'])->assertStatus(422)->assertJsonPath('code', 'TP_LIMIT');
        $this->assertSame(10, Tp::where('id_rombel', $this->rombel->id_rombel)->count());
    }

    public function test_hapus_tp_bernilai_ditolak_dan_urutan_tetap_rapat(): void
    {
        [$t1, $t2, $t3] = $this->buatTp(3);
        $this->sebagai($this->userGuru, 'GURU');
        $this->isi($this->sts, [[$this->peserta[0], $t1, 80]])->assertOk();

        $this->deleteJson("/api/guru/tp/{$t1->id_tp}")->assertStatus(422)->assertJsonPath('code', 'LOCKED');
        $this->deleteJson("/api/guru/tp/{$t2->id_tp}")->assertOk();
        $this->assertSame(2, $t3->fresh()->urutan);
    }

    public function test_urutan_tp_bisa_digeser(): void
    {
        [$t1, $t2, $t3] = $this->buatTp(3);
        $this->sebagai($this->userGuru, 'GURU')
            ->putJson("/api/guru/rombel/{$this->rombel->id_rombel}/tp-urutan", ['id_tp' => [$t3->id_tp, $t1->id_tp, $t2->id_tp]])->assertOk();

        $this->assertSame([1, 2, 3], [$t3->fresh()->urutan, $t1->fresh()->urutan, $t2->fresh()->urutan]);
    }

    public function test_salin_tp_sebagai_baris_baru_hanya_ke_mapel_sama_yang_diampu(): void
    {
        [$t1, $t2] = $this->buatTp(2);
        $tujuan = Rombel::create(['id_semester' => $this->rombel->id_semester, 'id_mapel' => $this->rombel->id_mapel, 'nama' => 'Matematika X-2']);
        $tujuan->guru()->attach($this->guruMapel->id_guru);
        $this->buatTp(1, $tujuan);
        $lain = Rombel::create(['id_semester' => $this->rombel->id_semester, 'id_mapel' => Mapel::create(['kode' => 'FIS', 'nama' => 'Fisika'])->id_mapel, 'nama' => 'Fisika X']);
        $lain->guru()->attach($this->guruMapel->id_guru);
        $this->sebagai($this->userGuru, 'GURU');
        $url = "/api/guru/rombel/{$this->rombel->id_rombel}/tp-salin";

        $this->postJson($url, ['id_tp' => [$t1->id_tp, $t2->id_tp], 'id_rombel_tujuan' => [$tujuan->id_rombel]])->assertOk();
        $this->assertSame([1, 2, 3], $tujuan->tp()->pluck('urutan')->all());
        $this->assertNotContains($t1->id_tp, $tujuan->tp()->pluck('id_tp')->all());

        $this->postJson($url, ['id_tp' => [$t1->id_tp], 'id_rombel_tujuan' => [$lain->id_rombel]])->assertStatus(422);
    }

    public function test_guru_bukan_pengampu_ditolak(): void
    {
        $lain = $this->buatUser(['GURU']);
        $this->guru($lain);

        $this->sebagai($lain, 'GURU')->getJson("/api/guru/rombel/{$this->rombel->id_rombel}/tp")->assertForbidden()->assertJsonPath('code', 'NOT_PENGAMPU');
        $this->putJson("/api/guru/rombel/{$this->rombel->id_rombel}/nilai", ['id_periode' => $this->sts->id_periode, 'items' => []])->assertForbidden();
    }

    // ------------------------------------------------------------------ input nilai

    public function test_input_nilai_rentang_dan_null_mengosongkan(): void
    {
        [$t1] = $this->buatTp(1);
        $this->sebagai($this->userGuru, 'GURU');

        $this->isi($this->sts, [[$this->peserta[0], $t1, 101]])->assertStatus(422)->assertJsonValidationErrors('items.0.nilai');
        $this->isi($this->sts, [[$this->peserta[0], $t1, 0]])->assertOk()->assertJsonPath('data.tersimpan', 1);
        $this->assertDatabaseHas('nilai_tp', ['id_tp' => $t1->id_tp, 'nilai' => 0, 'id_periode' => $this->sts->id_periode]);
        $this->isi($this->sts, [[$this->peserta[0], $t1, null]])->assertOk()->assertJsonPath('data.dihapus', 1);
    }

    public function test_pratinjau_tempel_tidak_menyimpan(): void
    {
        [$t1, $t2] = $this->buatTp(2);
        $this->sebagai($this->userGuru, 'GURU')->postJson("/api/guru/rombel/{$this->rombel->id_rombel}/nilai/pratinjau", [
            'id_periode' => $this->sts->id_periode,
            'items' => [
                ['id_siswa_rombel' => $this->peserta[0]->id_siswa_rombel, 'id_tp' => $t1->id_tp, 'nilai' => 80],
                ['id_siswa_rombel' => $this->peserta[1]->id_siswa_rombel, 'id_tp' => $t2->id_tp, 'nilai' => 77.5],
            ],
        ])->assertOk()->assertJsonPath('data.jumlah_sel', 2);

        $this->assertDatabaseCount('nilai_tp', 0);
    }

    public function test_periode_tertutup_menolak_input(): void
    {
        [$t1] = $this->buatTp(1);
        $this->sebagai($this->userGuru, 'GURU');

        $this->isi($this->sas, [[$this->peserta[0], $t1, 80]])->assertStatus(422)->assertJsonPath('code', 'NILAI_DITOLAK');
        $this->sts->update(['tgl_tutup' => now()->subMinute()]);
        $this->isi($this->sts->fresh(), [[$this->peserta[0], $t1, 80]])->assertStatus(422);
    }

    public function test_tp_sts_saja_dan_nilai_sts_tidak_bisa_diubah_di_periode_sas(): void
    {
        [$t1, $t2] = $this->buatTp(2);
        $t2->update(['hanya_sts' => true]);
        $this->sebagai($this->userGuru, 'GURU');
        $this->isi($this->sts, [[$this->peserta[0], $t1, 70]])->assertOk();
        $this->bukaSas();

        $this->isi($this->sas, [[$this->peserta[0], $t2, 80]])->assertStatus(422)->assertJsonPath('errors', fn ($e) => str_contains(json_encode($e), 'hanya dinilai pada periode STS'));
        $this->isi($this->sas, [[$this->peserta[0], $t1, 99]])->assertStatus(422);
        $this->assertDatabaseHas('nilai_tp', ['id_tp' => $t1->id_tp, 'nilai' => 70]);
    }

    // ------------------------------------------------------------------ paket, verifikasi, kunci

    public function test_alur_kirim_setujui_kunci_dan_nilai_terkunci_menolak_edit(): void
    {
        [$t1, $t2] = $this->buatTp(2);
        $this->sebagai($this->userGuru, 'GURU');
        $this->isi($this->sts, [[$this->peserta[0], $t1, 80], [$this->peserta[0], $t2, 90], [$this->peserta[1], $t1, 70]])->assertOk();
        $kirim = "/api/guru/rombel/{$this->rombel->id_rombel}/paket/kirim";
        $body = ['id_periode' => $this->sts->id_periode, 'id_kelas' => $this->kelasXA->id_kelas];

        // Belum lengkap (peserta 2 belum TP2) → butuh konfirmasi.
        $this->postJson($kirim, $body)->assertStatus(422)->assertJsonPath('code', 'PAKET_BELUM_LENGKAP');
        $this->isi($this->sts, [[$this->peserta[1], $t2, 60]])->assertOk();
        $this->postJson($kirim, $body)->assertOk()->assertJsonPath('data.paket.status', 'terkirim')->assertJsonPath('data.kelengkapan.lengkap', true);
        $this->assertTrue(Notifikasi::where('id_user', $this->userWali->id_user)->where('jenis', 'nilai_dikirim')->exists());

        // Setelah dikirim, guru tidak bisa mengubah.
        $this->isi($this->sts, [[$this->peserta[0], $t1, 85]])->assertStatus(422);

        $paket = PaketNilai::firstOrFail();
        $this->sebagai($this->userWali, 'WALI_KELAS')->postJson("/api/wali/paket/{$paket->id_paket_nilai}/setujui")->assertOk()->assertJsonPath('data.status', 'disetujui');

        $this->sebagai($this->userKurikulum)->postJson("/api/kurikulum/periode/{$this->sts->id_periode}/kelas/{$this->kelasXA->id_kelas}/kunci")->assertOk();
        $this->assertSame('dikunci', $paket->fresh()->status);
        $this->assertSame('dikunci', $this->sts->fresh()->status);

        // Kunci nilai menolak edit.
        $this->sebagai($this->userGuru, 'GURU');
        $this->isi($this->sts, [[$this->peserta[0], $t1, 99]])->assertStatus(422);
        $this->assertDatabaseHas('nilai_tp', ['id_tp' => $t1->id_tp, 'id_siswa_rombel' => $this->peserta[0]->id_siswa_rombel, 'nilai' => 80]);
        $this->postJson("/api/guru/rombel/{$this->rombel->id_rombel}/tp", ['deskripsi' => 'TP tambahan'])->assertCreated(); // STS dikunci tidak membekukan TP baru untuk SAS

        // Buka koreksi (alasan wajib) → guru boleh memperbaiki meski periode sudah dikunci/ditutup.
        $this->sebagai($this->userKurikulum)->postJson("/api/kurikulum/paket/{$paket->id_paket_nilai}/buka-koreksi")->assertStatus(422);
        $this->postJson("/api/kurikulum/paket/{$paket->id_paket_nilai}/buka-koreksi", ['catatan' => 'Salah ketik nilai'])->assertOk()->assertJsonPath('data.koreksi', true);
        $this->sts->update(['tgl_tutup' => now()->subMinute()]);
        $this->sebagai($this->userGuru, 'GURU');
        $this->isi($this->sts->fresh(), [[$this->peserta[0], $t1, 88]])->assertOk();
    }

    public function test_wali_wajib_catatan_saat_minta_revisi_dan_hanya_kelasnya(): void
    {
        [$t1] = $this->buatTp(1);
        $this->sebagai($this->userGuru, 'GURU');
        $this->isi($this->sts, [[$this->peserta[0], $t1, 80], [$this->peserta[1], $t1, 70]])->assertOk();
        $this->postJson("/api/guru/rombel/{$this->rombel->id_rombel}/paket/kirim", ['id_periode' => $this->sts->id_periode, 'id_kelas' => $this->kelasXA->id_kelas])->assertOk();
        $paket = PaketNilai::firstOrFail();

        $this->sebagai($this->userWali, 'WALI_KELAS');
        $this->postJson("/api/wali/paket/{$paket->id_paket_nilai}/revisi")->assertStatus(422)->assertJsonValidationErrors('catatan');
        $this->postJson("/api/wali/paket/{$paket->id_paket_nilai}/revisi", ['catatan' => 'Nilai siswa 2 tidak wajar'])->assertOk()->assertJsonPath('data.status', 'perlu_revisi');
        $this->getJson("/api/wali/paket/{$paket->id_paket_nilai}")->assertOk()->assertJsonCount(2, 'data.riwayat');

        $waliLain = $this->buatUser(['WALI_KELAS']);
        $this->guru($waliLain);
        $this->sebagai($waliLain, 'WALI_KELAS')->getJson("/api/wali/paket/{$paket->id_paket_nilai}")->assertForbidden()->assertJsonPath('code', 'NOT_WALI_KELAS');
        $this->getJson("/api/wali/paket?id_periode={$this->sts->id_periode}")->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_kunci_kelas_ditolak_bila_belum_semua_disetujui(): void
    {
        [$t1] = $this->buatTp(1);
        $this->sebagai($this->userGuru, 'GURU');
        $this->isi($this->sts, [[$this->peserta[0], $t1, 80]])->assertOk();

        $this->sebagai($this->userKurikulum)->postJson("/api/kurikulum/periode/{$this->sts->id_periode}/kelas/{$this->kelasXA->id_kelas}/kunci")
            ->assertStatus(422)->assertJsonPath('code', 'PAKET_BELUM_SIAP');
        $this->getJson("/api/kurikulum/periode/{$this->sts->id_periode}/ringkasan")->assertOk()->assertJsonPath('data.kelas.0.status', 'pengisian');
    }

    // ------------------------------------------------------------------ rumus

    public function test_rumus_nilai_sts_sas_dan_akhir(): void
    {
        [$t1, $t2, $t3, $t4] = $this->buatTp(4);
        $this->sebagai($this->userGuru, 'GURU');
        $p = $this->peserta[0];
        $this->isi($this->sts, [[$p, $t1, 80], [$p, $t2, 90]])->assertOk();
        $this->bukaSas();
        $this->isi($this->sas, [[$p, $t3, 70], [$p, $t4, 100]])->assertOk();

        $hasil = NilaiService::hitung($this->rombel->tp()->get(), app(NilaiService::class)->nilaiPeserta(collect([$p->id_siswa_rombel]))->get($p->id_siswa_rombel), $this->sts->id_periode);

        $this->assertSame(85.0, $hasil['nilai_sts']);   // rata-rata nilai yang diisi di periode STS
        $this->assertSame(100.0, $hasil['nilai_sas']);  // TP urutan terbesar
        $this->assertSame(85.0, $hasil['nilai_akhir']); // rata-rata semua TP
        $this->assertTrue($hasil['lengkap']);
    }
}
