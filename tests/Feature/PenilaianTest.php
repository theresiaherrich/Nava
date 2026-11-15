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
use App\Models\WaliKelas;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class PenilaianTest extends TestCase
{
    use RefreshDatabase;

    private Semester $semester;

    private Kelas $kelas;

    private Rombel $rombel;

    private Tp $tpSts;

    private Tp $tpSas;

    private SiswaRombel $peserta;

    private PeriodeNilai $periode;

    private User $guruUser;

    protected function beforeRefreshingDatabase(): void
    {
        // Pengaman ini berjalan sebelum RefreshDatabase menjalankan migration.
        if (! app()->environment('testing')
            || config('database.default') !== 'sqlite'
            || config('database.connections.sqlite.driver') !== 'sqlite'
            || config('database.connections.sqlite.database') !== ':memory:'
            || ! empty(config('database.connections.sqlite.url'))) {
            throw new \RuntimeException('Pengujian dihentikan: wajib menggunakan SQLite in-memory pada environment testing.');
        }
    }

    protected function setUp(): void
    {
        parent::setUp();
        // Key sementara untuk cookie terenkripsi; tidak mengubah file environment.
        config(['app.key' => 'base64:'.base64_encode(random_bytes(32))]);
        $this->seed(RoleSeeder::class);

        $tahun = TahunAjaran::create(['nama' => '2026/2027', 'aktif' => true]);
        $this->semester = Semester::create(['tahun_ajaran_id' => $tahun->id, 'nama' => 'ganjil', 'aktif' => true]);
        $this->kelas = Kelas::create(['semester_id' => $this->semester->id, 'nama' => 'X-1', 'tingkat' => 10, 'fase' => 'E']);
        $mapel = Mapel::create(['kode' => 'BIND', 'nama' => 'Bahasa Indonesia']);
        $this->rombel = Rombel::create(['semester_id' => $this->semester->id, 'mapel_id' => $mapel->id, 'nama' => 'Bahasa Indonesia X']);

        $this->guruUser = $this->akun('guru');
        $guru = $this->buatGuru($this->guruUser, 'Guru Uji', 'ASN');
        $this->rombel->gurus()->attach($guru->id);

        $siswa = Siswa::create(['nis' => '1001', 'nama' => 'Siswa Uji', 'aktif' => true]);
        $sk = SiswaKelas::create(['siswa_id' => $siswa->id, 'kelas_id' => $this->kelas->id, 'semester_id' => $this->semester->id]);
        $this->peserta = SiswaRombel::create(['rombel_id' => $this->rombel->id, 'siswa_kelas_id' => $sk->id]);

        $this->tpSts = Tp::create(['rombel_id' => $this->rombel->id, 'nomor_urut' => 1, 'deskripsi' => 'Menulis teks deskripsi', 'masuk_sts' => true, 'is_sas' => false]);
        $this->tpSas = Tp::create(['rombel_id' => $this->rombel->id, 'nomor_urut' => 2, 'deskripsi' => 'Menganalisis teks', 'masuk_sts' => false, 'is_sas' => true]);

        $this->periode = PeriodeNilai::create([
            'semester_id' => $this->semester->id, 'jenis' => 'STS', 'tingkat' => 10,
            'tanggal_buka' => now()->subDay(), 'tanggal_tutup' => now()->addDay(),
        ]);
    }

    private function buatGuru(User $user, string $nama, string $status): Guru
    {
        $guru = new Guru(['nama' => $nama, 'status_kepegawaian' => $status, 'aktif' => true]);
        $guru->forceFill(['user_id' => $user->id])->save();

        return $guru;
    }

    private function akun(string $role): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->roles()->attach(Role::where('name', $role)->firstOrFail());

        return $user;
    }

    private function simpanNilai(User $user, ?float $skor, int $tpId): TestResponse
    {
        return $this->actingAs($user)->putJson("/api/rombel/{$this->rombel->id}/nilai", [
            'periode_id' => $this->periode->id,
            'items' => [['siswa_rombel_id' => $this->peserta->id, 'tp_id' => $tpId, 'skor' => $skor]],
        ]);
    }

    public function test_pratinjau_mempertahankan_nol_dan_null_tanpa_query_tulis(): void
    {
        $this->tpSas->update(['nomor_urut' => 4]);
        $tp = Tp::create(['rombel_id' => $this->rombel->id, 'nomor_urut' => 2, 'deskripsi' => 'TP tambahan STS', 'masuk_sts' => true]);
        Tp::create(['rombel_id' => $this->rombel->id, 'nomor_urut' => 3, 'deskripsi' => 'TP bagian SAS']);
        $this->actingAs($this->guruUser);
        // Listener dipasang setelah fixture siap; hanya SELECT diizinkan selama pratinjau.
        DB::listen(function ($query) {
            $this->assertMatchesRegularExpression('/^\s*select\b/i', $query->sql);
        });
        $this->postJson("/api/rombel/{$this->rombel->id}/nilai/pratinjau-tempel", [
            'periode_id' => $this->periode->id,
            'items' => [
                ['siswa_rombel_id' => $this->peserta->id, 'tp_id' => $this->tpSts->id, 'skor' => 0],
                ['siswa_rombel_id' => $this->peserta->id, 'tp_id' => $tp->id, 'skor' => ''],
            ],
        ])->assertOk()->assertJsonPath('data.items.0.skor', 0)
            ->assertJsonPath('data.items.1.skor', null)->assertJsonPath('data.jumlah_sel', 2);
        $this->assertDatabaseCount('nilai_tp', 0);
    }

    public function test_pratinjau_dan_put_memakai_validasi_yang_sama(): void
    {
        $sel = ['siswa_rombel_id' => $this->peserta->id, 'tp_id' => $this->tpSts->id];
        $this->actingAs($this->guruUser);
        foreach (['POST', 'PUT'] as $method) {
            $url = "/api/rombel/{$this->rombel->id}/nilai".($method === 'POST' ? '/pratinjau-tempel' : '');
            foreach ([$sel, $sel + ['skor' => -1], $sel + ['skor' => 101], $sel + ['skor' => '85,5'], $sel + ['skor' => '85.555']] as $item) {
                $this->json($method, $url, ['periode_id' => $this->periode->id, 'items' => [$item]])
                    ->assertUnprocessable()->assertJsonValidationErrors('items.0.skor');
            }
            $this->json($method, $url, ['periode_id' => $this->periode->id, 'items' => [$sel + ['skor' => 0], $sel + ['skor' => 10]]])
                ->assertUnprocessable()->assertJsonValidationErrors('items.1.tp_id');
        }
        $this->assertDatabaseCount('nilai_tp', 0);
    }

    public function test_pratinjau_dan_put_menolak_konteks_atau_paket_tidak_valid(): void
    {
        $sel = ['siswa_rombel_id' => $this->peserta->id, 'tp_id' => $this->tpSts->id, 'skor' => 80];
        $this->actingAs($this->guruUser);
        $cek = function () use ($sel) {
            foreach (['POST', 'PUT'] as $method) {
                $url = "/api/rombel/{$this->rombel->id}/nilai".($method === 'POST' ? '/pratinjau-tempel' : '');
                $this->json($method, $url, ['periode_id' => $this->periode->id, 'items' => [$sel]])->assertUnprocessable();
            }
        };
        $this->periode->update(['tingkat' => 11]);
        $cek();
        $semester = Semester::create(['tahun_ajaran_id' => $this->semester->tahun_ajaran_id, 'nama' => 'genap', 'aktif' => false]);
        $this->periode->update(['tingkat' => 10, 'semester_id' => $semester->id]);
        $cek();
        $this->periode->update(['semester_id' => $this->semester->id, 'dikunci' => true]);
        $cek();
        $this->periode->update(['dikunci' => false, 'tanggal_tutup' => now()->subHour()]);
        $cek();
        $this->periode->update(['tanggal_tutup' => now()->addDay()]);
        $paket = PaketNilai::create(['rombel_id' => $this->rombel->id, 'periode_id' => $this->periode->id, 'kelas_id' => $this->kelas->id, 'status' => 'dikirim']);
        foreach (['dikirim', 'disetujui', 'dikunci'] as $status) {
            $paket->update(['status' => $status]);
            $cek();
        }
        $this->assertDatabaseCount('nilai_tp', 0);
    }

    public function test_pratinjau_menolak_bukan_pengampu_dan_tp_rombel_lain(): void
    {
        $url = "/api/rombel/{$this->rombel->id}/nilai/pratinjau-tempel";
        $body = ['periode_id' => $this->periode->id, 'items' => [
            ['siswa_rombel_id' => $this->peserta->id, 'tp_id' => $this->tpSts->id, 'skor' => 100],
        ]];
        $this->actingAs($this->akun('guru'))->postJson($url, $body)->assertForbidden();
        $lain = Rombel::create(['semester_id' => $this->semester->id, 'mapel_id' => $this->rombel->mapel_id, 'nama' => 'Rombel lain']);
        $tp = Tp::create(['rombel_id' => $lain->id, 'nomor_urut' => 1, 'deskripsi' => 'TP rombel lain', 'masuk_sts' => true]);
        $body['items'][0]['tp_id'] = $tp->id;
        $this->actingAs($this->guruUser)->postJson($url, $body)->assertUnprocessable();
        $peserta = SiswaRombel::create(['rombel_id' => $lain->id, 'siswa_kelas_id' => $this->peserta->siswa_kelas_id]);
        $body['items'][0]['tp_id'] = $this->tpSts->id;
        $body['items'][0]['siswa_rombel_id'] = $peserta->id;
        $this->postJson($url, $body)->assertUnprocessable();
        $this->assertDatabaseCount('nilai_tp', 0);
    }

    public function test_pratinjau_dapat_dikirim_ke_put_dengan_batas_skor_dan_null(): void
    {
        $this->actingAs($this->guruUser);
        foreach ([0, 100, 85.25, null] as $skor) {
            $body = ['periode_id' => $this->periode->id, 'items' => [
                ['siswa_rombel_id' => $this->peserta->id, 'tp_id' => $this->tpSts->id, 'skor' => $skor],
            ]];
            $preview = $this->postJson("/api/rombel/{$this->rombel->id}/nilai/pratinjau-tempel", $body)->assertOk();
            $this->putJson("/api/rombel/{$this->rombel->id}/nilai", [
                'periode_id' => $preview->json('data.periode_id'),
                'items' => $preview->json('data.items'),
            ])->assertOk();
            $nilai = NilaiTp::firstOrFail();
            if ($skor === null) {
                $this->assertNull($nilai->skor);
            } else {
                $this->assertEquals($skor, (float) $nilai->skor);
            }
        }
        $this->assertDatabaseCount('nilai_tp', 1);
    }

    public function test_items_harus_list_pada_pratinjau_dan_put(): void
    {
        $sel = ['siswa_rombel_id' => $this->peserta->id, 'tp_id' => $this->tpSts->id, 'skor' => 80];
        $this->actingAs($this->guruUser);
        foreach (['POST', 'PUT'] as $method) {
            $url = "/api/rombel/{$this->rombel->id}/nilai".($method === 'POST' ? '/pratinjau-tempel' : '');
            foreach ([['barisA' => $sel], [0 => $sel, 2 => $sel]] as $items) {
                $this->json($method, $url, ['periode_id' => $this->periode->id, 'items' => $items])
                    ->assertUnprocessable()->assertJsonValidationErrors('items');
            }
        }
        $this->assertDatabaseCount('nilai_tp', 0);
    }

    public function test_put_menolak_tp_dan_peserta_di_luar_rombel_atau_tidak_ada(): void
    {
        $lain = Rombel::create(['semester_id' => $this->semester->id, 'mapel_id' => $this->rombel->mapel_id, 'nama' => 'Rombel lain']);
        $tp = Tp::create(['rombel_id' => $lain->id, 'nomor_urut' => 1, 'deskripsi' => 'TP rombel lain', 'masuk_sts' => true]);
        $peserta = SiswaRombel::create(['rombel_id' => $lain->id, 'siswa_kelas_id' => $this->peserta->siswa_kelas_id]);
        $sel = ['siswa_rombel_id' => $this->peserta->id, 'tp_id' => $this->tpSts->id, 'skor' => 80];
        $this->actingAs($this->guruUser);
        foreach (['tp_id' => $tp->id, 'siswa_rombel_id' => $peserta->id] as $field => $id) {
            foreach ([$id, 999999] as $invalidId) {
                $this->putJson("/api/rombel/{$this->rombel->id}/nilai", [
                    'periode_id' => $this->periode->id, 'items' => [array_replace($sel, [$field => $invalidId])],
                ])->assertUnprocessable()->assertJsonValidationErrors("items.0.{$field}");
            }
        }
        $this->assertDatabaseCount('nilai_tp', 0);
    }

    public function test_get_nilai_memfilter_peserta_sesuai_tingkat_periode(): void
    {
        $kelas = Kelas::create(['semester_id' => $this->semester->id, 'nama' => 'XI-1', 'tingkat' => 11, 'fase' => 'F']);
        $siswa = Siswa::create(['nis' => '1002', 'nama' => 'Siswa XI', 'aktif' => true]);
        $sk = SiswaKelas::create(['siswa_id' => $siswa->id, 'kelas_id' => $kelas->id, 'semester_id' => $this->semester->id]);
        $peserta = SiswaRombel::create(['rombel_id' => $this->rombel->id, 'siswa_kelas_id' => $sk->id]);
        $periode = PeriodeNilai::create([
            'semester_id' => $this->semester->id, 'jenis' => 'STS', 'tingkat' => 11,
            'tanggal_buka' => now()->subDay(), 'tanggal_tutup' => now()->addDay(),
        ]);
        $this->actingAs($this->guruUser);
        foreach ([$this->periode->id => $this->peserta->id, $periode->id => $peserta->id] as $periodeId => $pesertaId) {
            $this->getJson("/api/rombel/{$this->rombel->id}/nilai?periode_id={$periodeId}")
                ->assertOk()->assertJsonCount(1, 'data.peserta')->assertJsonPath('data.peserta.0.id', $pesertaId);
        }
    }

    public function test_batch_tidak_menyimpan_sebagian_jika_ada_sel_tidak_valid(): void
    {
        $this->simpanNilai($this->guruUser, 50, $this->tpSts->id)->assertOk();
        $valid = ['siswa_rombel_id' => $this->peserta->id, 'tp_id' => $this->tpSts->id, 'skor' => 90];
        $invalid = array_replace($valid, ['tp_id' => 999999]);
        // Cakup ID tidak ada, skor tidak valid, dan TP yang tidak berlaku di STS.
        foreach ([$invalid, array_replace($invalid, ['skor' => 101]), array_replace($valid, ['tp_id' => $this->tpSas->id])] as $sel) {
            $this->putJson("/api/rombel/{$this->rombel->id}/nilai", [
                'periode_id' => $this->periode->id, 'items' => [$valid, $sel],
            ])->assertUnprocessable();
            $this->assertDatabaseCount('nilai_tp', 1);
            $this->assertEquals(50, (float) NilaiTp::firstOrFail()->skor);
        }
    }

    public function test_paket_perlu_perbaikan_mengizinkan_pratinjau_dan_simpan(): void
    {
        $paket = PaketNilai::create(['rombel_id' => $this->rombel->id, 'periode_id' => $this->periode->id, 'kelas_id' => $this->kelas->id, 'status' => 'perlu_perbaikan']);
        $body = ['periode_id' => $this->periode->id, 'items' => [
            ['siswa_rombel_id' => $this->peserta->id, 'tp_id' => $this->tpSts->id, 'skor' => 80],
        ]];
        $this->actingAs($this->guruUser)->postJson("/api/rombel/{$this->rombel->id}/nilai/pratinjau-tempel", $body)->assertOk();
        $this->assertDatabaseCount('nilai_tp', 0);
        $this->putJson("/api/rombel/{$this->rombel->id}/nilai", $body)->assertOk()->assertJsonPath('data.tersimpan', 1);
        $this->assertEquals(80, (float) NilaiTp::firstOrFail()->skor);
        $this->assertSame('perlu_perbaikan', $paket->fresh()->status);
    }

    public function test_put_memeriksa_ulang_status_setelah_pratinjau(): void
    {
        $this->simpanNilai($this->guruUser, 50, $this->tpSts->id)->assertOk();
        $paket = PaketNilai::create(['rombel_id' => $this->rombel->id, 'periode_id' => $this->periode->id, 'kelas_id' => $this->kelas->id, 'status' => 'draft']);
        $body = ['periode_id' => $this->periode->id, 'items' => [
            ['siswa_rombel_id' => $this->peserta->id, 'tp_id' => $this->tpSts->id, 'skor' => 90],
        ]];
        $preview = $this->postJson("/api/rombel/{$this->rombel->id}/nilai/pratinjau-tempel", $body)->assertOk();
        $paket->update(['status' => 'dikirim']);
        $this->putJson("/api/rombel/{$this->rombel->id}/nilai", [
            'periode_id' => $preview->json('data.periode_id'), 'items' => $preview->json('data.items'),
        ])->assertUnprocessable();
        $this->assertEquals(50, (float) NilaiTp::firstOrFail()->skor);
    }

    public function test_guru_dapat_pratinjau_dan_simpan_tp_sas_pada_periode_sas(): void
    {
        $periode = PeriodeNilai::create([
            'semester_id' => $this->semester->id, 'jenis' => 'SAS', 'tingkat' => 10,
            'tanggal_buka' => now()->subDay(), 'tanggal_tutup' => now()->addDay(),
        ]);
        $body = ['periode_id' => $periode->id, 'items' => [
            ['siswa_rombel_id' => $this->peserta->id, 'tp_id' => $this->tpSas->id, 'skor' => 88.5],
        ]];
        $this->actingAs($this->guruUser)->postJson("/api/rombel/{$this->rombel->id}/nilai/pratinjau-tempel", $body)->assertOk();
        $this->putJson("/api/rombel/{$this->rombel->id}/nilai", $body)->assertOk();
        $this->assertDatabaseHas('nilai_tp', ['tp_id' => $this->tpSas->id, 'siswa_rombel_id' => $this->peserta->id, 'skor' => 88.5]);
        $this->assertDatabaseCount('nilai_tp', 1);
    }

    public function test_guru_pengampu_dapat_mengisi_nilai_tp_sts(): void
    {
        $this->simpanNilai($this->guruUser, 85, $this->tpSts->id)->assertOk();

        $this->assertEquals(85.0, (float) NilaiTp::firstOrFail()->skor);
    }

    public function test_guru_bukan_pengampu_ditolak(): void
    {
        $lain = $this->akun('guru');
        $guruLain = $this->buatGuru($lain, 'Guru Lain', 'GTT');

        $this->simpanNilai($lain, 80, $this->tpSts->id)->assertForbidden();
        $this->assertDatabaseCount('nilai_tp', 0);
    }

    public function test_nilai_di_luar_rentang_ditolak_dan_kosong_bukan_nol(): void
    {
        $this->simpanNilai($this->guruUser, 101, $this->tpSts->id)->assertUnprocessable();
        $this->simpanNilai($this->guruUser, null, $this->tpSts->id)->assertOk();

        $this->assertNull(NilaiTp::firstOrFail()->skor);
    }

    public function test_tp_yang_bukan_untuk_sts_tidak_dapat_diisi_pada_periode_sts(): void
    {
        $this->simpanNilai($this->guruUser, 70, $this->tpSas->id)->assertUnprocessable();
    }

    public function test_peserta_dari_semester_berbeda_ditolak(): void
    {
        $semester2 = Semester::create(['tahun_ajaran_id' => $this->semester->tahun_ajaran_id, 'nama' => 'genap', 'aktif' => false]);
        $kelas2 = Kelas::create(['semester_id' => $semester2->id, 'nama' => 'X-1', 'tingkat' => 10, 'fase' => 'E']);
        $siswa2 = Siswa::create(['nis' => '1002', 'nama' => 'Siswa Dua', 'aktif' => true]);
        $sk2 = SiswaKelas::create(['siswa_id' => $siswa2->id, 'kelas_id' => $kelas2->id, 'semester_id' => $semester2->id]);

        $this->actingAs($this->akun('kurikulum'))
            ->postJson("/api/rombel/{$this->rombel->id}/peserta", ['siswa_kelas_ids' => [$sk2->id]])
            ->assertUnprocessable();
    }

    public function test_alur_kirim_setujui_kunci_lalu_nilai_terkunci(): void
    {
        // Kirim ditolak karena TP STS belum lengkap.
        $this->actingAs($this->guruUser)->postJson('/api/paket-nilai/kirim', [
            'rombel_id' => $this->rombel->id, 'periode_id' => $this->periode->id, 'kelas_id' => $this->kelas->id,
        ])->assertUnprocessable();

        $this->simpanNilai($this->guruUser, 88, $this->tpSts->id)->assertOk();
        $this->actingAs($this->guruUser)->postJson('/api/paket-nilai/kirim', [
            'rombel_id' => $this->rombel->id, 'periode_id' => $this->periode->id, 'kelas_id' => $this->kelas->id,
        ])->assertOk();
        $paket = PaketNilai::firstOrFail();
        $this->assertSame('dikirim', $paket->status);

        // Wali kelas yang ditugaskan menyetujui.
        $waliUser = $this->akun('wali_kelas');
        $waliGuru = $this->buatGuru($waliUser, 'Wali Uji', 'ASN');
        WaliKelas::create(['kelas_id' => $this->kelas->id, 'guru_id' => $waliGuru->id]);
        $this->actingAs($waliUser)->postJson("/api/paket-nilai/{$paket->id}/setujui")->assertOk();

        // Kurikulum mengunci.
        $this->actingAs($this->akun('kurikulum'))->postJson("/api/paket-nilai/{$paket->id}/kunci")->assertOk();
        $this->assertSame('dikunci', $paket->fresh()->status);

        // Setelah dikunci, guru tidak bisa mengubah nilai.
        $this->simpanNilai($this->guruUser, 99, $this->tpSts->id)->assertUnprocessable();
        $this->assertEquals(88.0, (float) NilaiTp::firstOrFail()->skor);
    }
}
