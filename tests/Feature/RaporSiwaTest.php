<?php

namespace Tests\Feature;

use App\Models\Ekskul;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\NilaiTp;
use App\Models\PaketNilai;
use App\Models\PelengkapRapor;
use App\Models\PeriodeNilai;
use App\Models\Rapor;
use App\Models\Role;
use App\Models\Rombel;
use App\Models\Sekolah;
use App\Models\Semester;
use App\Models\Siswa;
use App\Models\SiswaKelas;
use App\Models\SiswaRombel;
use App\Models\TahunAjaran;
use App\Models\TemplateRapor;
use App\Models\Tp;
use App\Models\User;
use App\Models\WaliKelas;
use Database\Seeders\PredikatSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** Tes bagian 4.7 (pelengkap rapor, ekskul, presensi ringkasan) dan 4.8 (cetak dan arsip rapor). */
class RaporSiwaTest extends TestCase
{
    use RefreshDatabase;

    private Semester $semester;
    private Kelas $kelas;
    private Kelas $kelasLain;
    private PeriodeNilai $periode;
    private Rombel $rombel;
    private PaketNilai $paket;
    private SiswaKelas $skA;
    private SiswaKelas $skB;
    private User $wali;
    private User $waliLain;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->seed([RoleSeeder::class, PredikatSeeder::class]);

        Sekolah::create(['npsn' => '12345678', 'nama' => 'SMA Uji', 'alamat' => 'Jl. Uji 1', 'nama_kepala_sekolah' => 'Kepsek']);
        $tahun = TahunAjaran::create(['nama' => '2026/2027', 'aktif' => true]);
        $this->semester = Semester::create(['tahun_ajaran_id' => $tahun->id, 'nama' => 'ganjil', 'aktif' => true]);
        $this->kelas = Kelas::create(['semester_id' => $this->semester->id, 'nama' => 'X-1', 'tingkat' => 10, 'fase' => 'E']);
        $this->kelasLain = Kelas::create(['semester_id' => $this->semester->id, 'nama' => 'X-2', 'tingkat' => 10, 'fase' => 'E']);

        $this->wali = $this->akun('wali_kelas');
        WaliKelas::create(['kelas_id' => $this->kelas->id, 'guru_id' => $this->buatGuru($this->wali)->id]);
        $this->waliLain = $this->akun('wali_kelas');
        WaliKelas::create(['kelas_id' => $this->kelasLain->id, 'guru_id' => $this->buatGuru($this->waliLain)->id]);

        $mapel = Mapel::create(['kode' => 'MTK', 'nama' => 'Matematika']);
        $this->rombel = Rombel::create(['semester_id' => $this->semester->id, 'mapel_id' => $mapel->id, 'nama' => 'Matematika X']);
        $tp1 = Tp::create(['rombel_id' => $this->rombel->id, 'nomor_urut' => 1, 'deskripsi' => 'TP 1', 'masuk_sts' => true, 'is_sas' => false]);
        $tp2 = Tp::create(['rombel_id' => $this->rombel->id, 'nomor_urut' => 2, 'deskripsi' => 'TP 2', 'masuk_sts' => true, 'is_sas' => false]);

        $this->skA = $this->daftarkan('1001', 'Ani', $tp1, $tp2, 80, 90);
        $this->skB = $this->daftarkan('1002', 'Budi', $tp1, $tp2, 70, 71);

        $this->periode = PeriodeNilai::create([
            'semester_id' => $this->semester->id, 'jenis' => 'STS', 'tingkat' => 10,
            'tanggal_buka' => now()->subDay(), 'tanggal_tutup' => now()->addDay(),
        ]);
        $this->paket = PaketNilai::create(['rombel_id' => $this->rombel->id, 'periode_id' => $this->periode->id, 'kelas_id' => $this->kelas->id, 'status' => 'dikunci']);
    }

    private function akun(string $role): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->roles()->attach(Role::where('name', $role)->firstOrFail());

        return $user;
    }

    private function buatGuru(User $user): Guru
    {
        $guru = new Guru(['nama' => 'Guru '.$user->id, 'status_kepegawaian' => 'ASN', 'aktif' => true]);
        $guru->forceFill(['user_id' => $user->id])->save();

        return $guru;
    }

    private function daftarkan(string $nis, string $nama, Tp $tp1, Tp $tp2, float $s1, float $s2): SiswaKelas
    {
        $siswa = Siswa::create(['nis' => $nis, 'nama' => $nama, 'aktif' => true]);
        $sk = SiswaKelas::create(['siswa_id' => $siswa->id, 'kelas_id' => $this->kelas->id, 'semester_id' => $this->semester->id]);
        $sr = SiswaRombel::create(['rombel_id' => $this->rombel->id, 'siswa_kelas_id' => $sk->id]);
        NilaiTp::create(['tp_id' => $tp1->id, 'siswa_rombel_id' => $sr->id, 'skor' => $s1]);
        NilaiTp::create(['tp_id' => $tp2->id, 'siswa_rombel_id' => $sr->id, 'skor' => $s2]);

        return $sk;
    }

    private function templateSts(): TemplateRapor
    {
        return TemplateRapor::create(['id_sekolah' => 1, 'jenis' => 'STS', 'versi' => '1', 'nama' => 'Rapor STS']);
    }

    private function q(): array
    {
        return ['periode_id' => $this->periode->id, 'kelas_id' => $this->kelas->id];
    }

    // ---------- 4.7 Pelengkap rapor ----------

    public function test_pelengkap_index_wali_dan_kurikulum_boleh_wali_lain_ditolak(): void
    {
        $this->actingAs($this->wali)->getJson('/api/pelengkap-rapor?'.http_build_query($this->q()))
            ->assertOk()
            ->assertJsonPath('data.ringkasan.total_siswa', 2)
            ->assertJsonPath('data.ringkasan.kehadiran_terisi', false)
            ->assertJsonPath('data.siswa.0.nama', 'Ani')
            ->assertJsonPath('data.siswa.0.alpha', null);

        $this->actingAs($this->akun('kurikulum'))->getJson('/api/pelengkap-rapor?'.http_build_query($this->q()))->assertOk();
        $this->actingAs($this->waliLain)->getJson('/api/pelengkap-rapor?'.http_build_query($this->q()))->assertForbidden();
        $this->actingAs($this->akun('guru'))->getJson('/api/pelengkap-rapor?'.http_build_query($this->q()))->assertForbidden();
    }

    public function test_pelengkap_index_validasi_dan_filter(): void
    {
        $this->actingAs($this->wali)->getJson('/api/pelengkap-rapor')->assertStatus(422);
        $this->actingAs($this->wali)->getJson('/api/pelengkap-rapor?'.http_build_query($this->q() + ['status' => 'aneh']))->assertStatus(422);

        PelengkapRapor::create(['id_riwayat_kelas' => $this->skA->id, 'id_periode' => $this->periode->id, 'catatan_wali' => 'Baik sekali']);

        $this->actingAs($this->wali)->getJson('/api/pelengkap-rapor?'.http_build_query($this->q() + ['status' => 'lengkap']))
            ->assertOk()->assertJsonCount(1, 'data.siswa')->assertJsonPath('data.siswa.0.nama', 'Ani');
        $this->actingAs($this->wali)->getJson('/api/pelengkap-rapor?'.http_build_query($this->q() + ['status' => 'belum']))
            ->assertOk()->assertJsonCount(1, 'data.siswa')->assertJsonPath('data.siswa.0.nama', 'Budi');
        $this->actingAs($this->wali)->getJson('/api/pelengkap-rapor?'.http_build_query($this->q() + ['q' => 'bud']))
            ->assertOk()->assertJsonCount(1, 'data.siswa');

        // Periode tingkat lain ditolak.
        $p11 = PeriodeNilai::create(['semester_id' => $this->semester->id, 'jenis' => 'STS', 'tingkat' => 11, 'tanggal_buka' => now(), 'tanggal_tutup' => now()->addDay()]);
        $this->actingAs($this->wali)->getJson('/api/pelengkap-rapor?'.http_build_query(['kelas_id' => $this->kelas->id, 'periode_id' => $p11->id]))->assertStatus(422);
    }

    public function test_catatan_tersimpan_hanya_oleh_wali_kelasnya(): void
    {
        $url = "/api/pelengkap-rapor/siswa/{$this->skA->siswa_id}/catatan";

        $this->actingAs($this->wali)->postJson($url, $this->q() + ['catatan' => 'Rajin dan aktif'])
            ->assertOk()->assertJsonPath('data.lengkap', true);
        $this->assertDatabaseHas('pelengkap_rapor', ['id_riwayat_kelas' => $this->skA->id, 'id_periode' => $this->periode->id, 'catatan_wali' => 'Rajin dan aktif']);

        // Ubah catatan = update, bukan baris baru.
        $this->actingAs($this->wali)->postJson($url, $this->q() + ['catatan' => 'Revisi'])->assertOk();
        $this->assertSame(1, PelengkapRapor::count());

        $this->actingAs($this->waliLain)->postJson($url, $this->q() + ['catatan' => 'x'])->assertForbidden();
        $this->actingAs($this->akun('kurikulum'))->postJson($url, $this->q() + ['catatan' => 'x'])->assertForbidden();
        $this->actingAs($this->wali)->postJson($url, $this->q() + ['catatan' => str_repeat('a', 301)])->assertStatus(422);
        $this->actingAs($this->wali)->postJson($url, $this->q())->assertStatus(422);
    }

    public function test_catatan_siswa_kelas_lain_ditolak_dan_sakit_izin_alpha_tidak_diisi_nol(): void
    {
        $asing = Siswa::create(['nis' => '9999', 'nama' => 'Asing', 'aktif' => true]);
        SiswaKelas::create(['siswa_id' => $asing->id, 'kelas_id' => $this->kelasLain->id, 'semester_id' => $this->semester->id]);

        $this->actingAs($this->wali)->postJson("/api/pelengkap-rapor/siswa/{$asing->id}/catatan", $this->q() + ['catatan' => 'x'])->assertStatus(422);

        $this->actingAs($this->wali)->postJson("/api/pelengkap-rapor/siswa/{$this->skA->siswa_id}/catatan", $this->q() + ['catatan' => 'ok'])->assertOk();
        $baris = PelengkapRapor::firstOrFail();
        $this->assertNull($baris->sakit);
        $this->assertNull($baris->izin);
        $this->assertNull($baris->alpha);
    }

    public function test_simpan_sekaligus_dengan_transaksi_dan_pembatasan_kelas(): void
    {
        $this->actingAs($this->wali)->postJson('/api/pelengkap-rapor/simpan', $this->q() + ['items' => [
            ['siswa_id' => $this->skA->siswa_id, 'catatan' => 'Catatan A'],
            ['siswa_id' => $this->skB->siswa_id, 'catatan' => 'Catatan B', 'kokurikuler' => 'Proyek P5'],
        ]])->assertOk()->assertJsonPath('data.tersimpan', 2);
        $this->assertSame(2, PelengkapRapor::count());
        $this->assertDatabaseHas('pelengkap_rapor', ['id_riwayat_kelas' => $this->skB->id, 'kokurikuler' => 'Proyek P5']);

        // Satu siswa dari kelas lain: seluruh penyimpanan dibatalkan.
        $asing = Siswa::create(['nis' => '9999', 'nama' => 'Asing', 'aktif' => true]);
        SiswaKelas::create(['siswa_id' => $asing->id, 'kelas_id' => $this->kelasLain->id, 'semester_id' => $this->semester->id]);
        $this->actingAs($this->wali)->postJson('/api/pelengkap-rapor/simpan', $this->q() + ['items' => [
            ['siswa_id' => $this->skA->siswa_id, 'catatan' => 'Ubah A'],
            ['siswa_id' => $asing->id, 'catatan' => 'x'],
        ]])->assertStatus(422);
        $this->assertDatabaseHas('pelengkap_rapor', ['id_riwayat_kelas' => $this->skA->id, 'catatan_wali' => 'Catatan A']);

        $this->actingAs($this->waliLain)->postJson('/api/pelengkap-rapor/simpan', $this->q() + ['items' => [['siswa_id' => $this->skA->siswa_id, 'catatan' => 'x']]])->assertForbidden();
    }

    public function test_presensi_ringkasan_hanya_baca_dan_tidak_mengarang_angka(): void
    {
        $this->actingAs($this->wali)->getJson('/api/presensi-ringkasan?kelas_id='.$this->kelas->id.'&periode_id='.$this->periode->id)
            ->assertOk()
            ->assertJsonPath('data.tabel_presensi_a1_tersedia', false)
            ->assertJsonPath('data.ringkasan.tersedia', 0)
            ->assertJsonPath('data.siswa.0.sakit', null);

        PelengkapRapor::create(['id_riwayat_kelas' => $this->skA->id, 'id_periode' => $this->periode->id])
            ->forceFill(['sakit' => 1, 'izin' => 0, 'alpha' => 2])->save();
        $this->actingAs($this->akun('kurikulum'))->getJson('/api/presensi-ringkasan?kelas_id='.$this->kelas->id.'&periode_id='.$this->periode->id)
            ->assertOk()->assertJsonPath('data.ringkasan.tersedia', 1)->assertJsonPath('data.siswa.0.alpha', 2);

        $this->actingAs($this->waliLain)->getJson('/api/presensi-ringkasan?kelas_id='.$this->kelas->id.'&periode_id='.$this->periode->id)->assertForbidden();
    }

    // ---------- 4.7 Ekskul ----------

    public function test_ekskul_akses_kesiswaan_dan_admin_saja(): void
    {
        $this->actingAs($this->wali)->getJson('/api/ekskul')->assertForbidden();
        $this->actingAs($this->akun('kurikulum'))->postJson('/api/ekskul', ['nama' => 'Pramuka'])->assertForbidden();

        foreach (['kesiswaan', 'admin'] as $role) {
            $user = $this->akun($role);
            $this->actingAs($user)->postJson('/api/ekskul', ['nama' => 'Pramuka '.$role])->assertCreated()->assertJsonPath('data.nama', 'Pramuka '.$role);
        }
        $this->actingAs($this->akun('kesiswaan'))->postJson('/api/ekskul', ['nama' => 'Pramuka kesiswaan'])->assertStatus(422);
        $this->actingAs($this->akun('kesiswaan'))->postJson('/api/ekskul', [])->assertStatus(422);
        $this->actingAs($this->akun('kesiswaan'))->getJson('/api/ekskul')
            ->assertOk()->assertJsonCount(2, 'data')->assertJsonCount(3, 'predikat');
    }

    public function test_peserta_ekskul_tambah_lalu_perbarui_dan_validasi_hubungan_siswa(): void
    {
        $kesiswaan = $this->akun('kesiswaan');
        $ekskul = Ekskul::create(['nama' => 'Basket']);
        $body = fn (array $items) => ['semester_id' => $this->semester->id, 'ekskul_id' => $ekskul->id_ekskul, 'items' => $items];

        $this->actingAs($kesiswaan)->putJson('/api/peserta-ekskul', $body([['siswa_id' => $this->skA->siswa_id, 'predikat' => 'B']]))
            ->assertOk()->assertJsonPath('data.dibuat', 1)->assertJsonPath('data.diperbarui', 0);

        $this->actingAs($kesiswaan)->putJson('/api/peserta-ekskul', $body([['siswa_id' => $this->skA->siswa_id, 'predikat' => 'SB', 'catatan' => 'Juara']]))
            ->assertOk()->assertJsonPath('data.dibuat', 0)->assertJsonPath('data.diperbarui', 1);
        $this->assertSame(1, \App\Models\PesertaEkskul::count());
        $this->assertDatabaseHas('peserta_ekskul', ['id_siswa' => $this->skA->siswa_id, 'catatan' => 'Juara', 'diisi_oleh' => $kesiswaan->id]);

        // Siswa belum punya kelas pada semester itu, predikat tidak valid, role salah.
        $tanpaKelas = Siswa::create(['nis' => '5555', 'nama' => 'Tanpa Kelas', 'aktif' => true]);
        $this->actingAs($kesiswaan)->putJson('/api/peserta-ekskul', $body([['siswa_id' => $tanpaKelas->id, 'predikat' => 'B']]))->assertStatus(422);
        $this->actingAs($kesiswaan)->putJson('/api/peserta-ekskul', $body([['siswa_id' => $this->skA->siswa_id, 'predikat' => 'X']]))->assertStatus(422);
        $this->actingAs($this->wali)->putJson('/api/peserta-ekskul', $body([['siswa_id' => $this->skA->siswa_id, 'predikat' => 'B']]))->assertForbidden();
    }

    public function test_ekskul_tampil_di_pelengkap_rapor_dan_data_rapor(): void
    {
        $ekskul = Ekskul::create(['nama' => 'Basket']);
        $this->actingAs($this->akun('kesiswaan'))->putJson('/api/peserta-ekskul', [
            'semester_id' => $this->semester->id, 'ekskul_id' => $ekskul->id_ekskul,
            'items' => [['siswa_id' => $this->skA->siswa_id, 'predikat' => 'SB']],
        ])->assertOk();

        $this->actingAs($this->wali)->getJson('/api/pelengkap-rapor?'.http_build_query($this->q()))
            ->assertJsonPath('data.siswa.0.ekskul.0.nama', 'Basket')
            ->assertJsonPath('data.siswa.0.ekskul.0.nilai', 'SB')
            ->assertJsonPath('data.ringkasan.ekskul_terisi', 1);
    }

    // ---------- 4.8 Cetak dan arsip rapor ----------

    public function test_cetak_index_menampilkan_nilai_dari_nilai_tp_dan_status_kunci(): void
    {
        $this->actingAs($this->wali)->getJson('/api/cetak-rapor?'.http_build_query($this->q()))
            ->assertOk()
            ->assertJsonPath('data.dikunci', true)
            ->assertJsonPath('data.total_siswa', 2)
            ->assertJsonPath('data.template_tersedia', false)
            ->assertJsonPath('data.siswa.0.rapor.mapel.0.mapel', 'Matematika')
            ->assertJsonPath('data.siswa.0.rapor.mapel.0.nilai_akhir', 85)
            ->assertJsonPath('data.siswa.1.rapor.mapel.0.nilai_akhir', 70.5);

        $this->paket->update(['status' => 'disetujui']);
        $this->actingAs($this->akun('kurikulum'))->getJson('/api/cetak-rapor?'.http_build_query($this->q()))
            ->assertOk()->assertJsonPath('data.dikunci', false)->assertJsonPath('data.mapel_belum_dikunci', 1)->assertJsonPath('data.siap_count', 0);

        $this->actingAs($this->waliLain)->getJson('/api/cetak-rapor?'.http_build_query($this->q()))->assertForbidden();
        $this->actingAs($this->akun('guru'))->getJson('/api/cetak-rapor?'.http_build_query($this->q()))->assertForbidden();
    }

    public function test_pdf_siswa_ditolak_bila_belum_dikunci_atau_tanpa_template(): void
    {
        $url = "/api/cetak-rapor/siswa/{$this->skA->siswa_id}/pdf";

        $this->actingAs($this->wali)->postJson($url, $this->q())->assertStatus(422); // template belum ada

        $this->templateSts();
        $this->paket->update(['status' => 'disetujui']);
        $this->actingAs($this->wali)->postJson($url, $this->q())->assertStatus(422); // nilai belum dikunci
        $this->assertSame(0, Rapor::count());
    }

    public function test_pdf_satu_siswa_membuat_arsip_dan_versi_hanya_naik_bila_data_berubah(): void
    {
        $this->templateSts();
        $url = "/api/cetak-rapor/siswa/{$this->skA->siswa_id}/pdf";

        $res = $this->actingAs($this->wali)->post($url, $this->q(), ['Accept' => 'application/json']);
        $res->assertOk()->assertHeader('Content-Type', 'application/pdf')->assertHeader('X-Rapor-Versi', 'v1');
        $this->assertStringStartsWith('%PDF', $res->getContent());
        $rapor = Rapor::firstOrFail();
        $this->assertSame('v1', $rapor->versi);
        $this->assertSame('siap', $rapor->status);
        $this->assertSame($this->wali->id, $rapor->diterbitkan_oleh);
        Storage::disk('local')->assertExists($rapor->file_pdf);
        $this->assertSame('Ani', $rapor->data_final['siswa']['nama']);

        // Cetak ulang tanpa perubahan data: tetap v1.
        $this->actingAs($this->wali)->post($url, $this->q(), ['Accept' => 'application/json'])->assertOk()->assertHeader('X-Rapor-Versi', 'v1');
        $this->assertSame(1, Rapor::count());

        // Catatan wali berubah: versi baru v2, v1 tetap ada.
        $this->actingAs($this->wali)->postJson("/api/pelengkap-rapor/siswa/{$this->skA->siswa_id}/catatan", $this->q() + ['catatan' => 'Tambahan'])->assertOk();
        $this->actingAs($this->wali)->post($url, $this->q(), ['Accept' => 'application/json'])->assertOk()->assertHeader('X-Rapor-Versi', 'v2');
        $this->assertSame(2, Rapor::count());
        Storage::disk('local')->assertExists($rapor->file_pdf);

        $this->actingAs($this->waliLain)->post($url, $this->q(), ['Accept' => 'application/json'])->assertForbidden();
    }

    public function test_pdf_siswa_dari_kelas_lain_ditolak(): void
    {
        $this->templateSts();
        $asing = Siswa::create(['nis' => '9999', 'nama' => 'Asing', 'aktif' => true]);
        SiswaKelas::create(['siswa_id' => $asing->id, 'kelas_id' => $this->kelasLain->id, 'semester_id' => $this->semester->id]);

        $this->actingAs($this->wali)->postJson("/api/cetak-rapor/siswa/{$asing->id}/pdf", $this->q())->assertStatus(422);
    }

    public function test_pdf_kelas_menghasilkan_satu_pdf_dan_arsip_semua_siswa(): void
    {
        $this->templateSts();

        $res = $this->actingAs($this->akun('kurikulum'))->post('/api/cetak-rapor/kelas/pdf', $this->q(), ['Accept' => 'application/json']);
        $res->assertOk()->assertHeader('Content-Type', 'application/pdf')->assertHeader('X-Rapor-Jumlah', '2');
        $this->assertStringStartsWith('%PDF', $res->getContent());
        $this->assertSame(2, Rapor::count());
        $this->assertSame(['v1', 'v1'], Rapor::orderBy('id_rapor')->pluck('versi')->all());

        $this->actingAs($this->waliLain)->post('/api/cetak-rapor/kelas/pdf', $this->q(), ['Accept' => 'application/json'])->assertForbidden();
    }

    public function test_riwayat_arsip_per_siswa_dan_pembatasan_akses(): void
    {
        $this->templateSts();
        $this->actingAs($this->wali)->post("/api/cetak-rapor/siswa/{$this->skA->siswa_id}/pdf", $this->q(), ['Accept' => 'application/json'])->assertOk();

        $this->actingAs($this->wali)->getJson('/api/cetak-rapor/riwayat?siswa_id='.$this->skA->siswa_id)
            ->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.versi', 'v1')
            ->assertJsonPath('data.0.jenis', 'STS')
            ->assertJsonPath('data.0.kelas', 'X-1')
            ->assertJsonPath('data.0.file_tersedia', true)
            ->assertJsonPath('data.0.dicetak_oleh', $this->wali->name);

        $this->actingAs($this->akun('kurikulum'))->getJson('/api/cetak-rapor/riwayat?siswa_id='.$this->skA->siswa_id)->assertOk()->assertJsonCount(1, 'data');
        $this->actingAs($this->wali)->getJson('/api/cetak-rapor/riwayat?siswa_id='.$this->skB->siswa_id)->assertOk()->assertJsonCount(0, 'data');
        $this->actingAs($this->waliLain)->getJson('/api/cetak-rapor/riwayat?siswa_id='.$this->skA->siswa_id)->assertForbidden();
        $this->actingAs($this->wali)->getJson('/api/cetak-rapor/riwayat')->assertStatus(422);
    }

    public function test_endpoint_butuh_login(): void
    {
        $this->getJson('/api/cetak-rapor')->assertUnauthorized();
        $this->getJson('/api/ekskul')->assertUnauthorized();
    }
}
