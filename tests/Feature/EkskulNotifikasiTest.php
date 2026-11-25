<?php

namespace Tests\Feature;

use App\Models\Ekskul;
use App\Models\Notifikasi;
use App\Models\PesertaEkskul;
use App\Models\TemplateRapor;
use App\Services\NotifikasiService;
use App\Services\RaporService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\SkenarioPenilaian;
use Tests\TestCase;

class EkskulNotifikasiTest extends TestCase
{
    use RefreshDatabase, SkenarioPenilaian;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siapkanSkenario();
    }

    public function test_kesiswaan_kelola_ekskul_peserta_dan_predikat(): void
    {
        $kesiswaan = $this->buatUser(['KESISWAAN']);
        $this->sebagai($kesiswaan);

        $id = $this->postJson('/api/kesiswaan/ekskul', ['nama' => 'Pramuka'])->assertCreated()->json('data.id_ekskul');
        $this->postJson("/api/kesiswaan/ekskul/{$id}/peserta", ['id_riwayat_kelas' => [$this->peserta[0]->id_riwayat_kelas, $this->peserta[1]->id_riwayat_kelas]])
            ->assertOk()->assertJsonPath('data.ditambahkan', 2);
        $peserta = $this->getJson("/api/kesiswaan/ekskul/{$id}/peserta")->assertOk()->assertJsonCount(2, 'data')->json('data');

        $this->putJson("/api/kesiswaan/ekskul/{$id}/predikat", ['items' => [['id_peserta_ekskul' => $peserta[0]['id_peserta_ekskul'], 'predikat' => 'A']]])
            ->assertStatus(422)->assertJsonValidationErrors('items.0.predikat');
        $this->putJson("/api/kesiswaan/ekskul/{$id}/predikat", ['items' => [['id_peserta_ekskul' => $peserta[0]['id_peserta_ekskul'], 'predikat' => 'SB', 'catatan' => 'Aktif']]])->assertOk();
        $this->assertDatabaseHas('peserta_ekskul', ['id_peserta_ekskul' => $peserta[0]['id_peserta_ekskul'], 'predikat' => 'SB']);

        $this->deleteJson("/api/kesiswaan/ekskul/{$id}")->assertStatus(422);
        $this->sebagai($this->userGuru, 'GURU')->getJson('/api/kesiswaan/ekskul')->assertForbidden();
    }

    public function test_predikat_ekskul_hanya_tampil_di_rapor_semester(): void
    {
        $e = Ekskul::create(['nama' => 'Basket']);
        PesertaEkskul::create(['id_ekskul' => $e->id_ekskul, 'id_riwayat_kelas' => $this->peserta[0]->id_riwayat_kelas, 'predikat' => 'B']);
        $svc = app(RaporService::class);
        $anggota = $this->peserta[0]->riwayatKelas;

        $sts = $svc->bangun($anggota, $this->sts, TemplateRapor::create(['jenis' => 'STS', 'nama' => 'S', 'view' => 'rapor.sts']));
        $sas = $svc->bangun($anggota, $this->sas, TemplateRapor::create(['jenis' => 'SAS', 'nama' => 'A', 'view' => 'rapor.semester']));

        $this->assertSame([], $sts['ekskul']);
        $this->assertSame('B', $sas['ekskul'][0]['predikat']);
    }

    public function test_notifikasi_milik_sendiri_dan_tandai_dibaca(): void
    {
        $svc = app(NotifikasiService::class);
        $svc->kirim($this->userGuru->id_user, 'uji', 'Judul', 'Pesan satu');
        $svc->kirim($this->userGuru->id_user, 'uji', 'Judul', 'Pesan dua');
        $svc->kirim($this->userWali->id_user, 'uji', 'Judul', 'Milik wali');
        $milikWali = Notifikasi::where('id_user', $this->userWali->id_user)->first();

        $this->sebagai($this->userGuru, 'GURU');
        $res = $this->getJson('/api/notifikasi')->assertOk()->assertJsonCount(2, 'data')->assertJsonPath('jumlah_belum_dibaca', 2);
        $this->postJson('/api/notifikasi/'.$res->json('data.0.id_notifikasi').'/baca')->assertOk();
        $this->getJson('/api/notifikasi?belum_dibaca=1')->assertJsonCount(1, 'data');
        $this->postJson("/api/notifikasi/{$milikWali->id_notifikasi}/baca")->assertNotFound();
        $this->postJson('/api/notifikasi/baca-semua')->assertOk()->assertJsonPath('data.ditandai', 1);
    }

    public function test_dashboard_setiap_peran_dapat_diakses(): void
    {
        $this->sebagai($this->buatUser(['ADMIN']))->getJson('/api/master/dashboard')->assertOk()->assertJsonStructure(['data' => ['jumlah_siswa', 'jumlah_guru', 'jumlah_kelas', 'akun_belum_aktif']]);
        $this->sebagai($this->userKurikulum)->getJson('/api/kurikulum/dashboard')->assertOk()->assertJsonCount(2, 'data.periode');
        $this->sebagai($this->userGuru, 'GURU')->getJson('/api/guru/dashboard')->assertOk()->assertJsonPath('data.jumlah_rombel', 1);
        $this->sebagai($this->userWali, 'WALI_KELAS')->getJson('/api/wali/dashboard')->assertOk()->assertJsonPath('data.kelas.0.nama', 'X-A');
        $this->sebagai($this->buatUser(['KESISWAAN']))->getJson('/api/kesiswaan/dashboard')->assertOk();

        $siswa = $this->buatUser(['SISWA']);
        $this->peserta[0]->riwayatKelas->siswa->forceFill(['id_user' => $siswa->id_user])->save();
        $this->sebagai($siswa)->getJson('/api/portal/beranda')->assertOk()->assertJsonPath('data.kelas.nama', 'X-A');
    }
}
