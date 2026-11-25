<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\SiakadFixtures;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase, SiakadFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReferensi();
    }

    public function test_login_berhasil_mengembalikan_peran_aktif_dan_csrf(): void
    {
        $this->buatUser(['GURU', 'WALI_KELAS'], ['username' => 'budi']);

        $this->postJson('/api/auth/login', ['username' => 'BUDI', 'password' => 'Rahasia-Uji-123'])
            ->assertOk()
            ->assertJsonPath('data.user.username', 'budi')
            ->assertJsonPath('data.peran_aktif', 'WALI_KELAS')
            ->assertJsonStructure(['data' => ['csrf_token', 'user' => ['roles']], 'message']);

        $this->assertAuthenticated();
    }

    public function test_login_salah_mengembalikan_401(): void
    {
        $this->buatUser(['GURU'], ['username' => 'budi']);

        $this->postJson('/api/auth/login', ['username' => 'budi', 'password' => 'salah'])
            ->assertStatus(401)->assertJsonPath('code', 'INVALID_CREDENTIALS');
        $this->postJson('/api/auth/login', ['username' => 'tidakada', 'password' => 'salah'])
            ->assertStatus(401);
        $this->assertGuest();
    }

    public function test_login_ditolak_untuk_akun_nonaktif_dan_belum_aktif(): void
    {
        $this->buatUser(['GURU'], ['username' => 'mati', 'status_akun' => 'nonaktif']);
        $this->buatUser(['GURU'], ['username' => 'baru', 'status_akun' => 'belum_aktif']);

        $this->postJson('/api/auth/login', ['username' => 'mati', 'password' => 'Rahasia-Uji-123'])
            ->assertForbidden()->assertJsonPath('code', 'ACCOUNT_DISABLED');
        $this->postJson('/api/auth/login', ['username' => 'baru', 'password' => 'Rahasia-Uji-123'])
            ->assertForbidden()->assertJsonPath('code', 'ACCOUNT_NOT_ACTIVATED')
            ->assertJsonPath('message', 'Akun belum aktif. Hubungi operator sekolah untuk aktivasi.');
        $this->assertGuest();
    }

    public function test_error_database_saat_login_tidak_ditampilkan_ke_pengguna(): void
    {
        $this->buatUser(['GURU'], ['username' => 'budi']);
        // Gagal SETELAH Auth::login() (audit login), mis. tabel belum dimigrasi.
        \Illuminate\Support\Facades\Schema::drop('log_aktivitas');
        \Illuminate\Support\Facades\Log::spy();

        $respons = $this->postJson('/api/auth/login', ['username' => 'budi', 'password' => 'Rahasia-Uji-123'])
            ->assertStatus(500)
            ->assertJsonPath('code', 'SERVER_ERROR')
            ->assertJsonPath('message', 'Terjadi gangguan pada server. Coba lagi beberapa saat lagi.');

        $this->assertStringNotContainsString('SQLSTATE', $respons->getContent());
        $this->assertStringNotContainsString('log_aktivitas', $respons->getContent());
        $this->assertGuest();
        \Illuminate\Support\Facades\Log::shouldHaveReceived('error')->once();
    }

    public function test_login_dibatasi_lima_kali_per_menit(): void
    {
        $this->buatUser(['GURU'], ['username' => 'budi']);

        foreach (range(1, 5) as $_) {
            $this->postJson('/api/auth/login', ['username' => 'budi', 'password' => 'salah'])->assertStatus(401);
        }
        $this->postJson('/api/auth/login', ['username' => 'budi', 'password' => 'salah'])
            ->assertStatus(429)->assertJsonPath('code', 'TOO_MANY_REQUESTS');
    }

    public function test_logout_mengakhiri_sesi(): void
    {
        $user = $this->buatUser(['GURU']);
        $this->sebagai($user)->postJson('/api/auth/logout')->assertOk();

        $this->assertGuest();
        $this->getJson('/api/auth/me')->assertStatus(401)->assertJsonPath('code', 'UNAUTHENTICATED');
    }

    public function test_wajib_ganti_sandi_memblokir_endpoint_lain(): void
    {
        $user = $this->buatUser(['SUPERADMIN'], ['must_change_password' => true]);
        $this->sebagai($user);

        $this->getJson('/api/auth/me')->assertOk()->assertJsonPath('data.must_change_password', true);
        $this->getJson('/api/akun')->assertForbidden()->assertJsonPath('code', 'MUST_CHANGE_PASSWORD');
        $this->postJson('/api/auth/switch-role', ['peran' => 'SUPERADMIN'])->assertForbidden()->assertJsonPath('code', 'MUST_CHANGE_PASSWORD');

        $this->postJson('/api/auth/password/change', [
            'password_lama' => 'Rahasia-Uji-123',
            'password_baru' => 'Sandi-Baru-456!',
            'password_baru_confirmation' => 'Sandi-Baru-456!',
        ])->assertOk();

        $this->assertFalse($user->fresh()->must_change_password);
        $this->getJson('/api/akun')->assertOk();
    }

    public function test_sandi_baru_lemah_ditolak(): void
    {
        $user = $this->buatUser(['GURU'], ['must_change_password' => true]);

        $this->sebagai($user)->postJson('/api/auth/password/change', [
            'password_lama' => 'Rahasia-Uji-123',
            'password_baru' => 'pendek',
            'password_baru_confirmation' => 'pendek',
        ])->assertStatus(422)->assertJsonValidationErrors('password_baru');
    }

    public function test_ganti_peran_hanya_ke_peran_yang_dimiliki(): void
    {
        $user = $this->buatUser(['GURU', 'WALI_KELAS']);
        $this->sebagai($user, 'GURU');

        $this->postJson('/api/auth/switch-role', ['peran' => 'WALI_KELAS'])->assertOk()->assertJsonPath('data.peran_aktif', 'WALI_KELAS');
        $this->postJson('/api/auth/switch-role', ['peran' => 'SUPERADMIN'])->assertStatus(422);
    }

    public function test_akun_dinonaktifkan_saat_sesi_hidup_langsung_dikeluarkan(): void
    {
        $user = $this->buatUser(['SUPERADMIN']);
        $this->sebagai($user);
        $user->update(['status_akun' => 'nonaktif']); // instance yang sama dengan guard

        $this->getJson('/api/auth/me')->assertForbidden()->assertJsonPath('code', 'ACCOUNT_INACTIVE');
    }

    public function test_registrasi_akun_a2_tidak_tersedia(): void
    {
        $this->postJson('/api/auth/register', ['username' => 'x'])->assertNotFound();
    }

    /** Pendaftaran mandiri orang tua milik modul A1/A4 (POST /api/register): akun baru belum aktif. */
    public function test_pendaftaran_orang_tua_a1_untuk_siswa_a2_belum_bisa_login(): void
    {
        $akunSiswa = $this->buatUser(['SISWA'], ['username' => 'siswa.ani']);
        $siswa = $this->siswa($akunSiswa, ['nisn' => '0012345678']);

        $this->postJson('/api/register', [
            'username' => 'ortu.ani', 'password' => 'Rahasia-Ortu-123', 'password_confirmation' => 'Rahasia-Ortu-123',
            'nama' => 'Ibu Ani', 'jenis_kelamin' => 'P', 'telepon' => '081234567890', 'alamat' => 'Malang',
            'hubungan' => 'Ibu', 'username_siswa' => 'siswa.ani', 'nisn' => '0012345678',
        ])->assertSuccessful();

        $ortu = \App\Models\User::where('username', 'ortu.ani')->firstOrFail();
        $this->assertSame('ORANG_TUA', $ortu->role);
        $this->assertSame([$siswa->id_siswa], $ortu->anak()->pluck('siswa.id_siswa')->all());

        $this->postJson('/api/auth/login', ['username' => 'ortu.ani', 'password' => 'Rahasia-Ortu-123'])
            ->assertForbidden()->assertJsonPath('code', 'ACCOUNT_NOT_ACTIVATED');
        $this->assertGuest();
    }
}
