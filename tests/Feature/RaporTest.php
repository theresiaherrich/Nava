<?php

namespace Tests\Feature;

use App\Models\Ekskul;
use App\Models\Notifikasi;
use App\Models\PaketNilai;
use App\Models\PesertaEkskul;
use App\Models\Rapor;
use App\Models\Sekolah;
use App\Models\TemplateRapor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Tests\Support\SkenarioPenilaian;
use Tests\TestCase;

class RaporTest extends TestCase
{
    use RefreshDatabase, SkenarioPenilaian;

    private User $siswaA;

    private User $siswaB;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->siapkanSkenario();
        Sekolah::create(['id_sekolah' => 1, 'nama' => 'SMA Negeri 1 Kota Malang', 'kota' => 'Malang']);
        TemplateRapor::create(['jenis' => 'STS', 'nama' => 'STS', 'view' => 'rapor.sts', 'aktif' => true]);

        $this->siswaA = $this->buatUser(['SISWA']);
        $this->siswaB = $this->buatUser(['SISWA']);
        $this->peserta[0]->riwayatKelas->siswa->forceFill(['id_user' => $this->siswaA->id_user])->save();
        $this->peserta[1]->riwayatKelas->siswa->forceFill(['id_user' => $this->siswaB->id_user])->save();
    }

    private function nilaiDikunci(): void
    {
        [$t1] = $this->buatTp(1);
        $this->sebagai($this->userGuru, 'GURU');
        $this->isi($this->sts, [[$this->peserta[0], $t1, 80], [$this->peserta[1], $t1, 65]])->assertOk();
        $this->postJson("/api/guru/rombel/{$this->rombel->id_rombel}/paket/kirim", ['id_periode' => $this->sts->id_periode, 'id_kelas' => $this->kelasXA->id_kelas])->assertOk();
        $paket = PaketNilai::firstOrFail();
        $this->sebagai($this->userWali, 'WALI_KELAS')->postJson("/api/wali/paket/{$paket->id_paket_nilai}/setujui")->assertOk();
        $this->sebagai($this->userKurikulum)->postJson("/api/kurikulum/periode/{$this->sts->id_periode}/kelas/{$this->kelasXA->id_kelas}/kunci")->assertOk();
    }

    private function cetak(int $peserta = 0): TestResponse
    {
        return $this->sebagai($this->userWali, 'WALI_KELAS')->postJson('/api/wali/rapor/cetak', [
            'id_periode' => $this->sts->id_periode, 'id_riwayat_kelas' => $this->peserta[$peserta]->id_riwayat_kelas,
        ]);
    }

    public function test_rapor_hanya_bisa_dicetak_setelah_nilai_dikunci(): void
    {
        $this->cetak()->assertStatus(422)->assertJsonPath('code', 'LOCKED');

        $this->nilaiDikunci();
        $this->cetak()->assertOk()->assertJsonPath('data.versi', 1)->assertJsonPath('data.versi_baru', true);
        $this->cetak()->assertOk()->assertJsonPath('data.versi', 1)->assertJsonPath('data.versi_baru', false);
    }

    public function test_cetak_satu_kelas_sebagai_pdf_gabungan(): void
    {
        $this->nilaiDikunci();
        $res = $this->sebagai($this->userWali, 'WALI_KELAS')->post('/api/wali/rapor/cetak-kelas', [
            'id_periode' => $this->sts->id_periode, 'id_kelas' => $this->kelasXA->id_kelas,
        ], ['Accept' => 'application/json'])->assertOk();

        $this->assertSame('application/pdf', $res->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF', $res->getContent());
        $this->assertSame(2, Rapor::count());
    }

    public function test_wali_hanya_melihat_kelasnya(): void
    {
        $this->nilaiDikunci();
        $waliLain = $this->buatUser(['WALI_KELAS']);
        $this->guru($waliLain);
        $q = "id_kelas={$this->kelasXA->id_kelas}&id_periode={$this->sts->id_periode}";

        $this->sebagai($this->userWali, 'WALI_KELAS')->getJson("/api/wali/dkn?{$q}")->assertOk()->assertJsonCount(2, 'data.siswa');
        $this->sebagai($waliLain, 'WALI_KELAS')->getJson("/api/wali/dkn?{$q}")->assertForbidden()->assertJsonPath('code', 'NOT_WALI_KELAS');
        $this->getJson("/api/wali/rapor?{$q}")->assertForbidden();
        $this->getJson("/api/wali/pelengkap?{$q}")->assertForbidden();
    }

    public function test_siswa_tidak_bisa_membuka_rapor_siswa_lain_dan_rapor_belum_terbit(): void
    {
        $this->nilaiDikunci();
        $idA = $this->cetak(0)->json('data.id_rapor');
        $idB = $this->cetak(1)->json('data.id_rapor');

        // Belum terbit → siswa A tidak bisa mengunduh rapornya sendiri.
        $this->sebagai($this->siswaA, 'SISWA')->get("/api/portal/rapor/{$idA}/unduh")->assertForbidden()->assertJsonPath('code', 'RAPOR_NOT_PUBLISHED');
        $this->getJson('/api/portal/rapor')->assertOk()->assertJsonCount(0, 'data');

        $this->sebagai($this->userKurikulum)->postJson('/api/kurikulum/rapor/terbitkan', ['id_periode' => $this->sts->id_periode, 'id_kelas' => $this->kelasXA->id_kelas])
            ->assertOk()->assertJsonPath('data.jumlah_terbit', 2);
        $this->assertTrue(Notifikasi::where('id_user', $this->siswaA->id_user)->where('jenis', 'rapor_terbit')->exists());

        $this->sebagai($this->siswaA, 'SISWA');
        $this->get("/api/portal/rapor/{$idA}/unduh")->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->get("/api/portal/rapor/{$idB}/unduh", ['Accept' => 'application/json'])->assertForbidden()->assertJsonPath('code', 'STUDENT_FORBIDDEN');
        $this->getJson('/api/portal/rapor')->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_koreksi_setelah_terbit_membuat_versi_baru_tanpa_mengubah_versi_lama(): void
    {
        $this->nilaiDikunci();
        $v1 = $this->cetak()->json('data.id_rapor');
        $this->sebagai($this->userKurikulum)->postJson('/api/kurikulum/rapor/terbitkan', ['id_periode' => $this->sts->id_periode, 'id_kelas' => $this->kelasXA->id_kelas])->assertOk();
        $snapshot = Rapor::find($v1)->data_final;

        $paket = PaketNilai::firstOrFail();
        $this->postJson("/api/kurikulum/paket/{$paket->id_paket_nilai}/buka-koreksi", ['catatan' => 'Perbaikan nilai TP1'])->assertOk();
        $this->sebagai($this->userGuru, 'GURU');
        $this->isi($this->sts->fresh(), [[$this->peserta[0], $this->rombel->tp()->first(), 90]])->assertOk();
        $this->postJson("/api/guru/rombel/{$this->rombel->id_rombel}/paket/kirim", ['id_periode' => $this->sts->id_periode, 'id_kelas' => $this->kelasXA->id_kelas])->assertOk();
        $this->sebagai($this->userWali, 'WALI_KELAS')->postJson("/api/wali/paket/{$paket->id_paket_nilai}/setujui")->assertOk();
        $this->sebagai($this->userKurikulum)->postJson("/api/kurikulum/periode/{$this->sts->id_periode}/kelas/{$this->kelasXA->id_kelas}/kunci")->assertOk();

        $this->cetak()->assertOk()->assertJsonPath('data.versi', 2);
        $this->assertEquals($snapshot, Rapor::find($v1)->data_final);
        $this->assertSame(80, (int) Rapor::find($v1)->data_final['mapel'][0]['nilai']);
    }

    public function test_orang_tua_hanya_anak_tertaut(): void
    {
        $ortu = $this->buatUser(['ORANG_TUA']);
        $this->orangTua($ortu, $this->peserta[0]->riwayatKelas->siswa);
        $this->sebagai($ortu, 'ORANG_TUA');

        $this->getJson('/api/portal/anak')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/portal/nilai')->assertOk();
        $this->getJson('/api/portal/nilai?id_siswa='.$this->peserta[1]->riwayatKelas->id_siswa)->assertForbidden();
    }

    public function test_rapor_sts_menyimpan_nilai_per_tp_dan_pratinjau_memakai_pdf_tersimpan(): void
    {
        $this->nilaiDikunci();
        $id = $this->cetak()->assertOk()->json('data.id_rapor');
        $rapor = Rapor::findOrFail($id);
        $data = $rapor->data_final;
        $this->assertEquals([1 => 80], $data['mapel'][0]['tp']);
        $this->assertEquals(80, $data['mapel'][0]['nilai']);
        $this->assertStringContainsString('TP1', view('rapor.sts', ['items' => [$data + ['versi' => 1]]])->render());

        $preview = $this->get("/api/wali/rapor/{$id}/unduh?pratinjau=1")->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('inline;', $preview->headers->get('Content-Disposition'));
        $this->assertSame(Storage::disk('local')->get($rapor->file_pdf), $preview->getContent());
    }

    public function test_rapor_semester_menghitung_tp_terakhir_sekali_dan_menampilkan_predikat(): void
    {
        $this->nilaiDikunci();
        $this->bukaSas();
        TemplateRapor::create(['jenis' => 'SAS', 'nama' => 'Semester', 'view' => 'rapor.semester', 'aktif' => true]);
        $ekskul = Ekskul::create(['nama' => 'Pramuka']);
        PesertaEkskul::create(['id_ekskul' => $ekskul->id_ekskul, 'id_riwayat_kelas' => $this->peserta[0]->id_riwayat_kelas, 'predikat' => 'SB']);

        $this->sebagai($this->userGuru, 'GURU');
        $idTp = $this->postJson("/api/guru/rombel/{$this->rombel->id_rombel}/tp", ['deskripsi' => 'Sumatif akhir semester'])
            ->assertCreated()->json('data.id_tp');
        $tp = $this->rombel->tp()->findOrFail($idTp);
        $this->isi($this->sas, [[$this->peserta[0], $tp, 100], [$this->peserta[1], $tp, 70]])->assertOk();
        $this->isi($this->sas, [[$this->peserta[0], $tp, 100]])->assertOk();
        $this->assertDatabaseCount('nilai_tp', 4);
        $paket = $this->postJson("/api/guru/rombel/{$this->rombel->id_rombel}/paket/kirim", [
            'id_periode' => $this->sas->id_periode, 'id_kelas' => $this->kelasXA->id_kelas,
        ])->assertOk()->json('data.paket.id_paket_nilai');

        $this->sebagai($this->userWali, 'WALI_KELAS')->postJson("/api/wali/paket/{$paket}/setujui")->assertOk();
        $this->sebagai($this->userKurikulum)->postJson("/api/kurikulum/periode/{$this->sas->id_periode}/kelas/{$this->kelasXA->id_kelas}/kunci")->assertOk();
        $id = $this->sebagai($this->userWali, 'WALI_KELAS')->postJson('/api/wali/rapor/cetak', [
            'id_periode' => $this->sas->id_periode, 'id_riwayat_kelas' => $this->peserta[0]->id_riwayat_kelas,
        ])->assertOk()->json('data.id_rapor');
        $rapor = Rapor::findOrFail($id);
        $this->assertEquals(90, $rapor->data_final['mapel'][0]['nilai']);
        $this->assertEquals(100, $rapor->data_final['mapel'][0]['nilai_sas']);
        $this->assertSame('SB', $rapor->data_final['ekskul'][0]['predikat']);
        $html = view('rapor.semester', ['items' => [$rapor->data_final + ['versi' => 1]]])->render();
        $this->assertStringContainsString($rapor->data_final['siswa']['nama'], $html);
        $this->assertStringContainsString('Pramuka', $html);
        $this->assertStringContainsString('>SB<', $html);
        $this->assertStringStartsWith('%PDF', Storage::disk('local')->get($rapor->file_pdf));
    }

    public function test_rapor_draf_tidak_dapat_diterbitkan_saat_koreksi_terbuka(): void
    {
        $this->nilaiDikunci();
        $this->cetak()->assertOk();
        $paket = PaketNilai::firstOrFail();
        $this->sebagai($this->userKurikulum)->postJson("/api/kurikulum/paket/{$paket->id_paket_nilai}/buka-koreksi", [
            'catatan' => 'Satu nilai akan dikoreksi.',
        ])->assertOk();
        $this->postJson('/api/kurikulum/rapor/terbitkan', [
            'id_periode' => $this->sts->id_periode, 'id_kelas' => $this->kelasXA->id_kelas,
        ])->assertUnprocessable()->assertJsonPath('code', 'LOCKED');
        $this->assertDatabaseHas('rapor', ['status' => 'draf']);
    }

    public function test_detail_nilai_menandai_perlu_penguatan(): void
    {
        [$t1] = $this->buatTp(1);
        $this->sebagai($this->userGuru, 'GURU');
        $this->isi($this->sts, [[$this->peserta[1], $t1, 60]])->assertOk();

        $this->sebagai($this->siswaB, 'SISWA')->getJson("/api/portal/nilai/{$this->rombel->id_rombel}")
            ->assertOk()->assertJsonPath('data.tp.0.perlu_penguatan', true)->assertJsonPath('data.tp.0.kktp', 75);
    }

    public function test_pelengkap_rapor_dan_status_presensi_a1(): void
    {
        $this->sebagai($this->userWali, 'WALI_KELAS');
        $q = "id_kelas={$this->kelasXA->id_kelas}&id_periode={$this->sts->id_periode}";

        // Modul A1 (Presensi) sudah terintegrasi: tabel presensi tersedia.
        $this->getJson("/api/wali/pelengkap?{$q}")->assertOk()->assertJsonPath('data.status_presensi_a1', 'tersedia');
        $this->putJson('/api/wali/pelengkap', [
            'id_periode' => $this->sts->id_periode, 'id_kelas' => $this->kelasXA->id_kelas,
            'items' => [['id_riwayat_kelas' => $this->peserta[0]->id_riwayat_kelas, 'sakit' => 1, 'izin' => 0, 'alpha' => 0, 'catatan_wali' => 'Pertahankan.']],
        ])->assertOk();
        $this->assertDatabaseHas('pelengkap_rapor', ['id_riwayat_kelas' => $this->peserta[0]->id_riwayat_kelas, 'sakit' => 1, 'sumber_presensi' => 'manual']);

        // Tarik rekap dari tabel presensi A1; data di luar rentang tanggal tidak dihitung.
        $idSiswa = \App\Models\RiwayatKelas::findOrFail($this->peserta[0]->id_riwayat_kelas)->id_siswa;
        foreach ([['2026-08-03', 'Sakit'], ['2026-08-04', 'Sakit'], ['2026-08-05', 'Izin'], ['2026-08-06', 'Alpa'], ['2026-08-07', 'Hadir'], ['2026-10-01', 'Sakit']] as [$tanggal, $status]) {
            \Illuminate\Support\Facades\DB::table('presensi')->insert(['id_siswa' => $idSiswa, 'tanggal' => $tanggal, 'status' => $status, 'metode' => 'QR']);
        }
        $this->postJson('/api/wali/pelengkap/tarik-presensi', ['id_periode' => $this->sts->id_periode, 'id_kelas' => $this->kelasXA->id_kelas, 'dari' => '2026-07-01', 'sampai' => '2026-09-30'])
            ->assertOk();
        $this->assertDatabaseHas('pelengkap_rapor', [
            'id_riwayat_kelas' => $this->peserta[0]->id_riwayat_kelas, 'sakit' => 2, 'izin' => 1, 'alpha' => 1, 'sumber_presensi' => 'a1',
        ]);
    }

    public function test_tarik_presensi_ditolak_bila_modul_a1_tidak_ada(): void
    {
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        foreach (['riwayat_perubahan_presensi', 'sakit', 'izin', 'presensi'] as $tabel) {
            \Illuminate\Support\Facades\Schema::dropIfExists($tabel);
        }
        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

        $this->sebagai($this->userWali, 'WALI_KELAS');
        $this->getJson("/api/wali/pelengkap?id_kelas={$this->kelasXA->id_kelas}&id_periode={$this->sts->id_periode}")
            ->assertOk()->assertJsonPath('data.status_presensi_a1', 'belum_tersedia');
        $this->postJson('/api/wali/pelengkap/tarik-presensi', ['id_periode' => $this->sts->id_periode, 'id_kelas' => $this->kelasXA->id_kelas, 'dari' => '2026-07-01', 'sampai' => '2026-09-30'])
            ->assertStatus(409)->assertJsonPath('code', 'PRESENSI_A1_UNAVAILABLE');
    }
}
