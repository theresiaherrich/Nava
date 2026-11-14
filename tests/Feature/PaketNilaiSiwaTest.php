<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\LogAktivitas;
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

/**
 * Tambahan Siwa untuk paket nilai: detail, revisi per siswa, pemetaan status FE, akses daftar.
 * Terpisah dari PenilaianTest agar pekerjaan Wilda tidak tersentuh.
 */
class PaketNilaiSiwaTest extends TestCase
{
    use RefreshDatabase;

    private Semester $semester;
    private Kelas $kelas;
    private Rombel $rombel;
    private Tp $tp;
    private PeriodeNilai $periode;
    private User $guruUser;
    private User $waliUser;
    private Siswa $siswaA;
    private Siswa $siswaB;

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
        $guru = $this->buatGuru($this->guruUser, 'Guru Uji');
        $this->rombel->gurus()->attach($guru->id);

        $this->waliUser = $this->akun('wali_kelas');
        $wali = $this->buatGuru($this->waliUser, 'Wali Uji');
        WaliKelas::create(['kelas_id' => $this->kelas->id, 'guru_id' => $wali->id]);

        $this->siswaA = Siswa::create(['nis' => '1001', 'nama' => 'Siswa A', 'aktif' => true]);
        $this->siswaB = Siswa::create(['nis' => '1002', 'nama' => 'Siswa B', 'aktif' => true]);
        $this->tp = Tp::create(['rombel_id' => $this->rombel->id, 'nomor_urut' => 1, 'deskripsi' => 'Menulis teks', 'masuk_sts' => true, 'is_sas' => false]);

        foreach ([$this->siswaA, $this->siswaB] as $siswa) {
            $sk = SiswaKelas::create(['siswa_id' => $siswa->id, 'kelas_id' => $this->kelas->id, 'semester_id' => $this->semester->id]);
            $peserta = SiswaRombel::create(['rombel_id' => $this->rombel->id, 'siswa_kelas_id' => $sk->id]);
            NilaiTp::create(['tp_id' => $this->tp->id, 'siswa_rombel_id' => $peserta->id, 'skor' => 80]);
        }

        $this->periode = PeriodeNilai::create([
            'semester_id' => $this->semester->id, 'jenis' => 'STS', 'tingkat' => 10,
            'tanggal_buka' => now()->subDay(), 'tanggal_tutup' => now()->addDay(),
        ]);
    }

    private function buatGuru(User $user, string $nama): Guru
    {
        $guru = new Guru(['nama' => $nama, 'status_kepegawaian' => 'ASN', 'aktif' => true]);
        $guru->forceFill(['user_id' => $user->id])->save();

        return $guru;
    }

    private function akun(string $role): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->roles()->attach(Role::where('name', $role)->firstOrFail());

        return $user;
    }

    private function kirimPaket(): PaketNilai
    {
        $this->actingAs($this->guruUser)->postJson('/api/paket-nilai/kirim', [
            'rombel_id' => $this->rombel->id, 'periode_id' => $this->periode->id, 'kelas_id' => $this->kelas->id,
        ])->assertOk()->assertJsonPath('data.status_fe', 'terkirim');

        return PaketNilai::firstOrFail();
    }

    public function test_detail_paket_dapat_dilihat_guru_pengampu_wali_dan_kurikulum(): void
    {
        $paket = $this->kirimPaket();

        foreach ([$this->guruUser, $this->waliUser, $this->akun('kurikulum'), $this->akun('admin')] as $user) {
            $this->actingAs($user)->getJson("/api/paket-nilai/{$paket->id}")
                ->assertOk()
                ->assertJsonPath('data.id', $paket->id)
                ->assertJsonPath('data.status', 'dikirim')
                ->assertJsonPath('data.status_fe', 'terkirim')
                ->assertJsonPath('data.tingkat_label', 'X')
                ->assertJsonPath('data.ringkasan.jumlah_peserta', 2)
                ->assertJsonPath('data.ringkasan.sel_kosong', 0)
                ->assertJsonCount(2, 'data.peserta');
        }
    }

    public function test_detail_paket_ditolak_untuk_guru_lain_wali_lain_dan_role_lain(): void
    {
        $paket = $this->kirimPaket();

        $guruLain = $this->akun('guru');
        $this->buatGuru($guruLain, 'Guru Lain');
        $this->actingAs($guruLain)->getJson("/api/paket-nilai/{$paket->id}")->assertForbidden();

        $waliLain = $this->akun('wali_kelas');
        $guruWaliLain = $this->buatGuru($waliLain, 'Wali Lain');
        $kelasLain = Kelas::create(['semester_id' => $this->semester->id, 'nama' => 'X-2', 'tingkat' => 10, 'fase' => 'E']);
        WaliKelas::create(['kelas_id' => $kelasLain->id, 'guru_id' => $guruWaliLain->id]);
        $this->actingAs($waliLain)->getJson("/api/paket-nilai/{$paket->id}")->assertForbidden();

        $this->actingAs($this->akun('siswa'))->getJson("/api/paket-nilai/{$paket->id}")->assertForbidden();
    }

    public function test_detail_menampilkan_nilai_kosong_sebagai_null_bukan_nol(): void
    {
        $paket = PaketNilai::create([
            'rombel_id' => $this->rombel->id, 'periode_id' => $this->periode->id, 'kelas_id' => $this->kelas->id, 'status' => 'draft',
        ]);
        NilaiTp::where('tp_id', $this->tp->id)->first()->update(['skor' => null]);

        $this->actingAs($this->guruUser)->getJson("/api/paket-nilai/{$paket->id}")
            ->assertOk()
            ->assertJsonPath('data.status_fe', 'sedang-diisi')
            ->assertJsonPath('data.ringkasan.sel_kosong', 1)
            ->assertJsonPath('data.peserta.0.lengkap', false)
            ->assertJsonPath('data.peserta.0.nilai.0.skor', null)
            ->assertJsonPath('data.peserta.1.lengkap', true);
    }

    public function test_revisi_dengan_siswa_ids_tersimpan_di_audit_dan_tampil_di_detail(): void
    {
        $paket = $this->kirimPaket();

        $this->actingAs($this->waliUser)->postJson("/api/paket-nilai/{$paket->id}/revisi", [
            'catatan' => 'Periksa kembali nilai siswa A.',
            'siswa_ids' => [$this->siswaA->id],
        ])->assertOk()
            ->assertJsonPath('data.status', 'perlu_perbaikan')
            ->assertJsonPath('data.status_fe', 'perlu-revisi');

        $log = LogAktivitas::where('aksi', 'minta_revisi')->where('entitas_id', $paket->id)->firstOrFail();
        $this->assertSame([$this->siswaA->id], $log->data_sesudah['siswa_ids']);
        $this->assertSame('perlu_perbaikan', $log->data_sesudah['status']);
        $this->assertDatabaseHas('verifikasi_nilai', ['paket_id' => $paket->id, 'keputusan' => 'perlu_revisi']);

        $this->actingAs($this->guruUser)->getJson("/api/paket-nilai/{$paket->id}")
            ->assertOk()
            ->assertJsonPath('data.siswa_revisi', [$this->siswaA->id]);
    }

    public function test_revisi_tanpa_siswa_ids_tetap_berlaku_untuk_seluruh_paket(): void
    {
        $paket = $this->kirimPaket();

        $this->actingAs($this->waliUser)->postJson("/api/paket-nilai/{$paket->id}/revisi", [
            'catatan' => 'Mohon diperiksa ulang.',
        ])->assertOk()->assertJsonPath('data.status', 'perlu_perbaikan');

        $this->assertSame([], LogAktivitas::where('aksi', 'minta_revisi')->firstOrFail()->data_sesudah['siswa_ids']);
    }

    public function test_revisi_dengan_siswa_di_luar_paket_atau_duplikat_ditolak(): void
    {
        $paket = $this->kirimPaket();
        $luar = Siswa::create(['nis' => '2001', 'nama' => 'Siswa Luar', 'aktif' => true]);

        $this->actingAs($this->waliUser)->postJson("/api/paket-nilai/{$paket->id}/revisi", [
            'catatan' => 'Periksa kembali.', 'siswa_ids' => [$luar->id],
        ])->assertUnprocessable()->assertJsonValidationErrors('siswa_ids');

        $this->actingAs($this->waliUser)->postJson("/api/paket-nilai/{$paket->id}/revisi", [
            'catatan' => 'Periksa kembali.', 'siswa_ids' => [$this->siswaA->id, $this->siswaA->id],
        ])->assertUnprocessable();

        $this->assertSame('dikirim', $paket->fresh()->status);
        $this->assertDatabaseCount('verifikasi_nilai', 0);
    }

    public function test_revisi_hanya_oleh_wali_kelas_yang_bersangkutan(): void
    {
        $paket = $this->kirimPaket();

        $waliLain = $this->akun('wali_kelas');
        $guruWaliLain = $this->buatGuru($waliLain, 'Wali Lain');
        $kelasLain = Kelas::create(['semester_id' => $this->semester->id, 'nama' => 'X-2', 'tingkat' => 10, 'fase' => 'E']);
        WaliKelas::create(['kelas_id' => $kelasLain->id, 'guru_id' => $guruWaliLain->id]);

        $this->actingAs($waliLain)->postJson("/api/paket-nilai/{$paket->id}/revisi", ['catatan' => 'Periksa kembali.'])->assertForbidden();
        $this->actingAs($this->guruUser)->postJson("/api/paket-nilai/{$paket->id}/revisi", ['catatan' => 'Periksa kembali.'])->assertForbidden();
        $this->assertSame('dikirim', $paket->fresh()->status);
    }

    public function test_revisi_tanpa_catatan_ditolak(): void
    {
        $paket = $this->kirimPaket();

        $this->actingAs($this->waliUser)->postJson("/api/paket-nilai/{$paket->id}/revisi", ['siswa_ids' => [$this->siswaA->id]])
            ->assertUnprocessable()->assertJsonValidationErrors('catatan');
    }

    public function test_daftar_paket_guru_hanya_rombelnya_dan_wali_hanya_kelasnya(): void
    {
        $this->kirimPaket();
        $url = "/api/paket-nilai?periode_id={$this->periode->id}";

        $this->actingAs($this->guruUser)->getJson($url)->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.status_fe', 'terkirim');
        $this->actingAs($this->waliUser)->getJson($url)->assertOk()->assertJsonCount(1, 'data');
        $this->actingAs($this->akun('kurikulum'))->getJson($url)->assertOk()->assertJsonCount(1, 'data');

        $guruLain = $this->akun('guru');
        $this->buatGuru($guruLain, 'Guru Lain');
        $this->actingAs($guruLain)->getJson($url)->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_status_fe_periode_tutup_untuk_paket_belum_dikirim(): void
    {
        PaketNilai::create([
            'rombel_id' => $this->rombel->id, 'periode_id' => $this->periode->id, 'kelas_id' => $this->kelas->id, 'status' => 'draft',
        ]);
        $this->periode->update(['tanggal_tutup' => now()->subHour()]);

        $this->actingAs($this->guruUser)->getJson("/api/paket-nilai?periode_id={$this->periode->id}")
            ->assertOk()
            ->assertJsonPath('data.0.status', 'draft')
            ->assertJsonPath('data.0.status_fe', 'periode-tutup')
            ->assertJsonPath('data.0.periode_tutup', true);
    }
}