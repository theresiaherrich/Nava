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

    protected function setUp(): void
    {
        parent::setUp();
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

    private function simpanNilai(User $user, ?float $skor, int $tpId): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($user)->putJson("/api/rombel/{$this->rombel->id}/nilai", [
            'periode_id' => $this->periode->id,
            'items' => [['siswa_rombel_id' => $this->peserta->id, 'tp_id' => $tpId, 'skor' => $skor]],
        ]);
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
        $waliGuru = $this->buatGuru($waliUser, 'Wali Uji', 'ASN');        WaliKelas::create(['kelas_id' => $this->kelas->id, 'guru_id' => $waliGuru->id]);
        $this->actingAs($waliUser)->postJson("/api/paket-nilai/{$paket->id}/setujui")->assertOk();

        // Kurikulum mengunci.
        $this->actingAs($this->akun('kurikulum'))->postJson("/api/paket-nilai/{$paket->id}/kunci")->assertOk();
        $this->assertSame('dikunci', $paket->fresh()->status);

        // Setelah dikunci, guru tidak bisa mengubah nilai.
        $this->simpanNilai($this->guruUser, 99, $this->tpSts->id)->assertUnprocessable();
        $this->assertEquals(88.0, (float) NilaiTp::firstOrFail()->skor);
    }
}
