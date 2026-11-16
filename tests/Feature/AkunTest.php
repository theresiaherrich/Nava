<?php

namespace Tests\Feature;

use App\Models\LogAktivitas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\SiakadFixtures;
use Tests\TestCase;

class AkunTest extends TestCase
{
    use RefreshDatabase, SiakadFixtures;

    private User $super;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReferensi();
        $this->super = $this->buatUser(['SUPERADMIN'], ['username' => 'root']);
        $this->admin = $this->buatUser(['ADMIN'], ['username' => 'operator']);
    }

    public function test_superadmin_membuat_akun_admin(): void
    {
        $res = $this->sebagai($this->super)->postJson('/api/akun', [
            'username' => 'Operator2', 'nama' => 'Operator Dua', 'roles' => ['ADMIN'],
        ])->assertCreated()
            ->assertJsonPath('data.akun.username', 'operator2')
            ->assertJsonPath('data.akun.must_change_password', true);

        $this->assertNotEmpty($res->json('data.password_awal'));
        $this->assertTrue(User::where('username', 'operator2')->first()->hasRole('ADMIN'));
        $this->assertDatabaseHas('log_aktivitas', ['aksi' => 'buat_akun', 'id_user' => $this->super->id_user]);
    }

    public function test_admin_gagal_membuat_admin_atau_superadmin(): void
    {
        $this->sebagai($this->admin);

        $this->postJson('/api/akun', ['username' => 'adm2', 'nama' => 'X', 'roles' => ['ADMIN']])
            ->assertForbidden()->assertJsonPath('code', 'ROLE_HIERARCHY');
        $this->postJson('/api/akun', ['username' => 'sup2', 'nama' => 'X', 'roles' => ['SUPERADMIN']])
            ->assertForbidden()->assertJsonPath('code', 'ROLE_CLI_ONLY');

        $this->assertDatabaseMissing('user', ['username' => 'adm2']);
        $this->assertDatabaseMissing('user', ['username' => 'sup2']);
    }

    public function test_admin_tidak_dapat_mengubah_akun_superadmin_atau_admin_lain(): void
    {
        $admin2 = $this->buatUser(['ADMIN']);
        $this->sebagai($this->admin);

        $this->patchJson("/api/akun/{$this->super->id_user}/status", ['status_akun' => 'nonaktif', 'alasan' => 'x'])->assertForbidden();
        $this->putJson("/api/akun/{$admin2->id_user}", ['nama' => 'Ganti'])->assertForbidden();
        $this->postJson("/api/akun/{$admin2->id_user}/reset-password")->assertForbidden();
        $this->putJson("/api/akun/{$admin2->id_user}/peran", ['roles' => ['KURIKULUM']])->assertForbidden();
    }

    public function test_wali_kelas_tidak_bisa_dipilih_manual(): void
    {
        $this->sebagai($this->super)->postJson('/api/akun', [
            'username' => 'guruw', 'nama' => 'G', 'roles' => ['WALI_KELAS'],
        ])->assertStatus(422)->assertJsonPath('code', 'ROLE_AUTOMATIC');
    }

    public function test_akun_guru_wajib_dan_otomatis_tertaut_ke_profil(): void
    {
        $guru = $this->guru();
        $this->sebagai($this->super);

        $this->postJson('/api/akun', ['username' => 'guru1', 'nama' => 'G', 'roles' => ['GURU']])
            ->assertStatus(422)->assertJsonPath('code', 'PROFILE_REQUIRED');

        $this->postJson('/api/akun', ['username' => 'guru1', 'nama' => 'G', 'roles' => ['GURU'], 'id_guru' => $guru->id_guru])
            ->assertCreated()->assertJsonPath('data.akun.profil.id_guru', $guru->id_guru);

        $this->postJson('/api/akun', ['username' => 'guru2', 'nama' => 'G', 'roles' => ['GURU'], 'id_guru' => $guru->id_guru])
            ->assertStatus(409)->assertJsonPath('code', 'PROFILE_ALREADY_LINKED');
    }

    public function test_tidak_bisa_mengelola_akun_sendiri_lewat_menu_akun(): void
    {
        $this->sebagai($this->super);

        $this->patchJson("/api/akun/{$this->super->id_user}/status", ['status_akun' => 'nonaktif', 'alasan' => 'x'])
            ->assertForbidden()->assertJsonPath('code', 'SELF_MANAGEMENT');
        $this->putJson("/api/akun/{$this->super->id_user}/peran", ['roles' => ['KURIKULUM']])
            ->assertForbidden()->assertJsonPath('code', 'SELF_MANAGEMENT');
    }

    public function test_superadmin_terakhir_tidak_dapat_dinonaktifkan(): void
    {
        $super2 = $this->buatUser(['SUPERADMIN']);
        $this->super->update(['status_akun' => 'nonaktif']); // tinggal super2 yang aktif

        $this->sebagai($this->buatUser(['SUPERADMIN']));
        // Ada tiga superadmin, dua aktif: menonaktifkan super2 masih boleh.
        $this->patchJson("/api/akun/{$super2->id_user}/status", ['status_akun' => 'nonaktif', 'alasan' => 'rotasi'])->assertOk();

        $this->assertSame(1, User::where('status_akun', 'aktif')->whereHas('roles', fn ($q) => $q->where('kode', 'SUPERADMIN'))->count());
    }

    public function test_reset_password_mewajibkan_ganti_sandi(): void
    {
        $target = $this->buatUser(['KURIKULUM']);

        $sandi = $this->sebagai($this->super)->postJson("/api/akun/{$target->id_user}/reset-password")
            ->assertOk()->json('data.password_sementara');

        $this->assertTrue($target->fresh()->must_change_password);
        $this->assertTrue(password_verify($sandi, $target->fresh()->password));
    }

    public function test_buat_akun_massal_siswa_memakai_nisn_dan_hanya_sekali(): void
    {
        $this->siswa(null, ['nis' => '1001', 'nisn' => '0081234561', 'nama' => 'Andi']);
        $this->siswa(null, ['nis' => '1002', 'nisn' => null, 'nama' => 'Bunga']);
        $this->sebagai($this->super);

        $res = $this->postJson('/api/akun/massal', ['jenis' => 'siswa'])->assertCreated()->assertJsonPath('data.jumlah', 2);
        $this->assertEqualsCanonicalizing(['0081234561', '1002'], array_column($res->json('data.kredensial'), 'username'));
        $this->assertTrue(User::where('username', '0081234561')->first()->must_change_password);

        $this->postJson('/api/akun/massal', ['jenis' => 'siswa'])->assertCreated()->assertJsonPath('data.jumlah', 0);
        $this->assertFalse(LogAktivitas::where('aksi', 'buat_akun_massal')->get()->contains(fn ($l) => str_contains(json_encode($l->data_sesudah), 'password')));
    }

    public function test_admin_tidak_bisa_buat_akun_massal_dengan_config_default(): void
    {
        $this->siswa();
        $this->sebagai($this->admin)->postJson('/api/akun/massal', ['jenis' => 'siswa'])->assertForbidden();
    }

    public function test_batas_admin_bisa_diubah_lewat_config(): void
    {
        config(['siakad.role_manageable.ADMIN' => ['SISWA']]);
        $siswa = $this->siswa();

        $this->sebagai($this->admin)->postJson('/api/akun', [
            'username' => 'sis1', 'nama' => 'S', 'roles' => ['SISWA'], 'id_siswa' => $siswa->id_siswa,
        ])->assertCreated();
    }

    public function test_peran_lain_tidak_bisa_mengakses_kelola_akun(): void
    {
        $this->sebagai($this->buatUser(['GURU']))->getJson('/api/akun')->assertForbidden()->assertJsonPath('code', 'ROLE_FORBIDDEN');
    }

    public function test_perintah_create_superadmin_menolak_bila_sudah_ada(): void
    {
        $this->artisan('siakad:create-superadmin', ['--username' => 'kedua'])->assertFailed();
        $this->assertDatabaseMissing('user', ['username' => 'kedua']);
    }

    public function test_perintah_create_superadmin_dengan_force_dan_sandi_kuat(): void
    {
        $this->artisan('siakad:create-superadmin', ['--username' => 'kedua', '--nama' => 'Kedua', '--email' => 'k@sekolah.id', '--force' => true])
            ->expectsQuestion('Kata sandi (min. 10 karakter, huruf besar, kecil, angka, simbol)', 'Sandi-Kuat-789!')
            ->expectsQuestion('Ulangi kata sandi', 'Sandi-Kuat-789!')
            ->assertSuccessful();

        $this->assertTrue(User::where('username', 'kedua')->first()->hasRole('SUPERADMIN'));
    }
}
