<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiakadAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $this->seed(RoleSeeder::class);
    }

    private function akun(string $username, string $kodeRole, string $status = 'aktif'): User
    {
        $user = User::create([
            'username' => $username,
            'password' => 'rahasia123',
            'nama' => ucfirst($username),
            'status_akun' => $status,
            'must_change_password' => false,
        ]);
        $user->roles()->attach(Role::where('kode', $kodeRole)->value('id_role'));

        return $user;
    }

    public function test_login_akun_aktif_berhasil(): void
    {
        $this->akun('guru1', 'GURU');

        $this->postJson('/api/auth/login', ['username' => 'guru1', 'password' => 'rahasia123'])
            ->assertOk()
            ->assertJsonPath('active_role', 'GURU');
    }

    public function test_akun_pending_tidak_bisa_login(): void
    {
        $this->akun('siswa1', 'SISWA', 'pending');

        $this->postJson('/api/auth/login', ['username' => 'siswa1', 'password' => 'rahasia123'])
            ->assertStatus(403);
    }

    public function test_guru_tidak_bisa_mengakses_halaman_operator(): void
    {
        $guru = $this->akun('guru2', 'GURU');

        $this->actingAs($guru)
            ->withSession(['active_role' => 'GURU'])
            ->getJson('/api/admin/accounts')
            ->assertStatus(403);
    }

    public function test_operator_dapat_mengakses_daftar_akun(): void
    {
        $operator = $this->akun('op1', 'ADMIN');

        $this->actingAs($operator)
            ->withSession(['active_role' => 'ADMIN'])
            ->getJson('/api/admin/accounts')
            ->assertOk();
    }
}
