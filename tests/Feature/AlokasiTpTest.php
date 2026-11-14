<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\NilaiTp;
use App\Models\PaketNilai;
use App\Models\PeriodeNilai;
use App\Models\Role;
use App\Models\Rombel;
use App\Models\Semester;
use App\Models\Siswa;
use App\Models\SiswaKelas;
use App\Models\SiswaRombel;
use App\Models\TahunAjaran;
use App\Models\Tp;
use App\Models\User;
use App\Services\AlokasiTpService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AlokasiTpTest extends TestCase
{
    use RefreshDatabase;

    private Rombel $rombel;

    private Kelas $kelas;

    private SiswaRombel $peserta;

    private User $guru;

    private User $admin;

    private PeriodeNilai $sts;

    private PeriodeNilai $sas;

    protected function beforeRefreshingDatabase(): void
    {
        if (! app()->environment('testing') || config('database.default') !== 'sqlite'
            || config('database.connections.sqlite.driver') !== 'sqlite'
            || config('database.connections.sqlite.database') !== ':memory:'
            || ! empty(config('database.connections.sqlite.url'))) {
            throw new \RuntimeException('STOP: pengujian wajib SQLite in-memory.');
        }
    }

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.key' => 'base64:'.base64_encode(random_bytes(32))]);
        $this->seed(RoleSeeder::class);
        $tahun = TahunAjaran::create(['nama' => '2026/2027']);
        $semester = Semester::create(['tahun_ajaran_id' => $tahun->id, 'nama' => 'ganjil']);
        $this->kelas = Kelas::create(['semester_id' => $semester->id, 'nama' => 'X-1', 'tingkat' => 10, 'fase' => 'E']);
        $mapel = Mapel::create(['kode' => 'BIO', 'nama' => 'Biologi']);
        $this->rombel = Rombel::create(['semester_id' => $semester->id, 'mapel_id' => $mapel->id, 'nama' => 'Biologi X']);
        $this->guru = User::factory()->create(['is_active' => true]);
        $this->guru->roles()->attach(Role::where('name', 'guru')->firstOrFail());
        $guru = new Guru(['nama' => 'Guru Uji', 'status_kepegawaian' => 'ASN', 'aktif' => true]);
        $guru->forceFill(['user_id' => $this->guru->id])->save();
        $this->rombel->gurus()->attach($guru->id);
        $this->admin = User::factory()->create(['is_active' => true]);
        $this->admin->roles()->attach(Role::where('name', 'kurikulum')->firstOrFail());
        $siswa = Siswa::create(['nis' => '001', 'nama' => 'Siswa', 'aktif' => true]);
        $sk = SiswaKelas::create(['siswa_id' => $siswa->id, 'kelas_id' => $this->kelas->id, 'semester_id' => $semester->id]);
        $this->peserta = SiswaRombel::create(['rombel_id' => $this->rombel->id, 'siswa_kelas_id' => $sk->id]);
        foreach (['STS', 'SAS'] as $jenis) {
            $periode = PeriodeNilai::create(['semester_id' => $semester->id, 'jenis' => $jenis, 'tingkat' => 10,
                'tanggal_buka' => now()->subDay(), 'tanggal_tutup' => now()->addDay()]);
            if ($jenis === 'STS') {
                $this->sts = $periode;
            } else {
                $this->sas = $periode;
            }
        }
    }

    private function tp(array $nomor): Collection
    {
        foreach (array_reverse($nomor) as $n) {
            Tp::create(['rombel_id' => $this->rombel->id, 'nomor_urut' => $n,
                'deskripsi' => 'Tujuan '.$n, 'masuk_sts' => false, 'is_sas' => $n === max($nomor)]);
        }

        return $this->rombel->tp()->get();
    }

    private function body(PeriodeNilai $periode, $tp, $skor): array
    {
        return ['periode_id' => $periode->id, 'items' => [['siswa_rombel_id' => $this->peserta->id, 'tp_id' => $tp->id, 'skor' => $skor]]];
    }

    private function kirim(PeriodeNilai $periode)
    {
        return $this->actingAs($this->guru)->postJson('/api/paket-nilai/kirim', [
            'periode_id' => $periode->id, 'rombel_id' => $this->rombel->id, 'kelas_id' => $this->kelas->id,
        ]);
    }

    public static function jumlah(): array
    {
        return array_map(fn ($n) => [$n], range(1, 10));
    }

    #[DataProvider('jumlah')]
    public function test_alokasi_get_pratinjau_put_tidak_bergantung_flag(int $jumlah): void
    {
        $tp = $this->tp(range(1, $jumlah));
        $url = '/api/rombel/'.$this->rombel->id.'/nilai';
        $this->actingAs($this->guru);
        foreach ([$this->sts, $this->sas] as $periode) {
            $expected = $periode->jenis === 'STS' ? $tp->take(intdiv($jumlah, 2)) : $tp->slice(intdiv($jumlah, 2));
            $this->getJson($url.'?periode_id='.$periode->id)->assertOk()
                ->assertJsonPath('data.tp_dapat_diisi', $expected->pluck('id')->values()->all());
            foreach ($tp as $t) {
                $boleh = $expected->contains('id', $t->id);
                $preview = $this->postJson($url.'/pratinjau-tempel', $this->body($periode, $t, 0));
                $save = $this->putJson($url, $this->body($periode, $t, 0));
                $boleh ? $preview->assertOk() : $preview->assertUnprocessable();
                $boleh ? $save->assertOk() : $save->assertUnprocessable();
            }
        }
        $this->assertDatabaseCount('nilai_tp', $jumlah);
        $this->assertFalse($tp->first()->fresh()->masuk_sts);
    }

    public function test_lima_tp_berlubang_contoh_nilai_dan_kelangsungan_sas(): void
    {
        $tp = $this->tp([1, 3, 5, 7, 10]);
        $this->assertSame([1, 3], AlokasiTpService::untukInput($tp, 'STS')->pluck('nomor_urut')->all());
        $url = '/api/rombel/'.$this->rombel->id.'/nilai';
        foreach ([80, 90, 70, 85, 95] as $i => $skor) {
            $p = $i < 2 ? $this->sts : $this->sas;
            $this->actingAs($this->guru)->putJson($url, $this->body($p, $tp[$i], $skor))->assertOk();
        }
        $this->kirim($this->sts)->assertOk();
        $this->kirim($this->sas)->assertOk();
        foreach ([$this->sts->id => '85.000000', $this->sas->id => '84.000000'] as $id => $hasil) {
            $this->actingAs($this->admin)->getJson('/api/rekap-nilai?periode_id='.$id)->assertOk()->assertJsonPath('data.0.nilai_akhir', $hasil);
        }
    }

    public function test_sas_wajib_semua_tp_dan_null_tidak_lengkap_tetapi_nol_sah(): void
    {
        $tp = $this->tp([1, 2, 3, 4, 5]);
        foreach ($tp->slice(2) as $t) {
            NilaiTp::create(['tp_id' => $t->id, 'siswa_rombel_id' => $this->peserta->id, 'skor' => 0]);
        }
        $this->kirim($this->sas)->assertUnprocessable();
        NilaiTp::create(['tp_id' => $tp[0]->id, 'siswa_rombel_id' => $this->peserta->id, 'skor' => 0]);
        $null = NilaiTp::create(['tp_id' => $tp[1]->id, 'siswa_rombel_id' => $this->peserta->id, 'skor' => null]);
        $this->kirim($this->sas)->assertUnprocessable();
        $null->update(['skor' => 0]);
        $this->kirim($this->sas)->assertOk();
    }

    public function test_paket_sas_melindungi_koreksi_sts_dan_kelas_lain_tidak_terpengaruh(): void
    {
        $tp = $this->tp([1, 2]);
        $url = '/api/rombel/'.$this->rombel->id.'/nilai';
        NilaiTp::create(['tp_id' => $tp[0]->id, 'siswa_rombel_id' => $this->peserta->id, 'skor' => 80]);
        $paket = PaketNilai::create(['rombel_id' => $this->rombel->id, 'periode_id' => $this->sas->id,
            'kelas_id' => $this->kelas->id, 'status' => 'dikunci']);
        $this->actingAs($this->guru);
        foreach (['dikirim', 'disetujui', 'dikunci'] as $status) {
            $paket->update(['status' => $status]);
            $this->postJson($url.'/pratinjau-tempel', $this->body($this->sts, $tp[0], 90))->assertUnprocessable();
            $this->putJson($url, $this->body($this->sts, $tp[0], 90))->assertUnprocessable();
        }
        $paket->update(['status' => 'perlu_perbaikan']);
        $this->putJson($url, $this->body($this->sts, $tp[0], 90))->assertOk();
        $this->sas->update(['dikunci' => true]);
        $this->putJson($url, $this->body($this->sts, $tp[0], 95))->assertUnprocessable();
        $this->assertEquals(90, (float) NilaiTp::firstOrFail()->skor);
        $this->sas->update(['dikunci' => false]);
        $kelasLain = Kelas::create(['semester_id' => $this->rombel->semester_id, 'nama' => 'X-2', 'tingkat' => 10, 'fase' => 'E']);
        PaketNilai::create(['rombel_id' => $this->rombel->id, 'periode_id' => $this->sas->id,
            'kelas_id' => $kelasLain->id, 'status' => 'dikunci']);
        $this->putJson($url, $this->body($this->sts, $tp[0], 95))->assertOk();
    }

    public function test_struktur_tp_dibekukan_setelah_ada_skor_nol(): void
    {
        $tp = $this->tp([1, 3]);
        NilaiTp::create(['tp_id' => $tp[0]->id, 'siswa_rombel_id' => $this->peserta->id, 'skor' => 0]);
        $this->actingAs($this->admin)->postJson('/api/rombel/'.$this->rombel->id.'/tp', ['nomor_urut' => 2, 'deskripsi' => 'TP Baru'])->assertUnprocessable();
        $this->putJson('/api/tp/'.$tp[1]->id, ['nomor_urut' => 2, 'deskripsi' => 'Pindah urutan'])->assertUnprocessable();
        $this->deleteJson('/api/tp/'.$tp[1]->id)->assertUnprocessable();
        $this->assertDatabaseCount('tp', 2);
    }

    public function test_create_delete_salin_dan_batas_sepuluhtp_sebelum_nilai(): void
    {
        $this->actingAs($this->admin);
        for ($i = 1; $i <= 10; $i++) {
            $this->postJson('/api/rombel/'.$this->rombel->id.'/tp', ['nomor_urut' => $i, 'deskripsi' => 'Tujuan '.$i])->assertCreated();
        }
        $this->postJson('/api/rombel/'.$this->rombel->id.'/tp', ['nomor_urut' => 11, 'deskripsi' => 'TP ke11'])->assertUnprocessable();
        $tp = $this->rombel->tp()->first();
        $this->deleteJson('/api/tp/'.$tp->id)->assertOk();
        $tujuan = Rombel::create(['semester_id' => $this->rombel->semester_id, 'mapel_id' => $this->rombel->mapel_id, 'nama' => 'Tujuan']);
        $this->postJson('/api/tp/salin', ['dari_rombel_id' => $this->rombel->id, 'ke_rombel_id' => $tujuan->id])->assertCreated();
        $this->assertSame(9, $tujuan->tp()->count());
        $this->assertSame(4, AlokasiTpService::untukInput($tujuan->tp()->get(), 'STS')->count());
    }

    public function test_paket_lama_tidak_lengkap_tidak_bisa_dikunci(): void
    {
        $this->tp([1, 2]);
        $paket = PaketNilai::create(['rombel_id' => $this->rombel->id, 'periode_id' => $this->sas->id, 'kelas_id' => $this->kelas->id, 'status' => 'disetujui']);
        $this->actingAs($this->admin)->postJson('/api/paket-nilai/'.$paket->id.'/kunci')->assertUnprocessable();
        $this->assertSame('disetujui', $paket->fresh()->status);
    }

    public function test_struktur_dan_salin_diblokir_paket_terkunci_dan_periode_terkunci(): void
    {
        $tp = $this->tp([1, 3]);
        $tujuan = Rombel::create(['semester_id' => $this->rombel->semester_id, 'mapel_id' => $this->rombel->mapel_id, 'nama' => 'Tujuan']);
        $paket = PaketNilai::create(['rombel_id' => $tujuan->id, 'periode_id' => $this->sts->id,
            'kelas_id' => $this->kelas->id, 'status' => 'dikunci']);
        $this->actingAs($this->admin)->postJson('/api/tp/salin', ['dari_rombel_id' => $this->rombel->id, 'ke_rombel_id' => $tujuan->id])->assertUnprocessable();
        $this->postJson('/api/rombel/'.$tujuan->id.'/tp', ['nomor_urut' => 1, 'deskripsi' => 'Tujuan baru'])->assertUnprocessable();
        $paket->delete();
        $this->sts->update(['dikunci' => true]);
        $this->deleteJson('/api/tp/'.$tp[0]->id)->assertUnprocessable();
        $this->assertSame(0, $tujuan->tp()->count());
    }

    public function test_salin_sumber_lebih_sepuluhtp_ditolak_dan_marker_bukan_alokasi(): void
    {
        $tp = $this->tp(range(1, 10));
        Tp::create(['rombel_id' => $this->rombel->id, 'nomor_urut' => 11, 'deskripsi' => 'Data impor tidak valid']);
        $tujuan = Rombel::create(['semester_id' => $this->rombel->semester_id, 'mapel_id' => $this->rombel->mapel_id, 'nama' => 'Tujuan']);
        $this->actingAs($this->admin)->postJson('/api/tp/salin', ['dari_rombel_id' => $this->rombel->id, 'ke_rombel_id' => $tujuan->id])->assertUnprocessable();
        $this->putJson('/api/tp/'.$tp[0]->id, ['nomor_urut' => 1, 'deskripsi' => 'Tujuan satu', 'is_sas' => true])->assertUnprocessable();
        $this->assertSame(0, $tujuan->tp()->count());
    }

    public function test_marker_tetap_satu_tp_terakhir_dan_bukan_pembatas_input_sas(): void
    {
        $tp = $this->tp([1, 2, 3]);
        $this->actingAs($this->admin)->postJson('/api/rombel/'.$this->rombel->id.'/tp', ['nomor_urut' => 4, 'deskripsi' => 'Tujuan empat'])->assertUnprocessable();
        $this->putJson('/api/tp/'.$tp[2]->id, ['nomor_urut' => 3, 'deskripsi' => 'Tujuan tiga', 'is_sas' => false])->assertOk();
        $this->postJson('/api/rombel/'.$this->rombel->id.'/tp', ['nomor_urut' => 4, 'deskripsi' => 'Tujuan empat', 'is_sas' => true])->assertCreated();
        $this->assertSame(2, AlokasiTpService::untukInput($this->rombel->tp()->get(), 'SAS')->count());
        $this->assertSame(1, $this->rombel->tp()->where('is_sas', true)->count());
    }
}
