<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\SiakadFixtures;
use Tests\TestCase;

/** POST /api/lupa-password (modul A1) hanya memberi tautan reset bila identitas cocok. */
class LupaPasswordA1Test extends TestCase
{
    use RefreshDatabase, SiakadFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReferensi();
    }

    public function test_tautan_reset_tidak_diberikan_bila_nisn_salah(): void
    {
        $akun = $this->buatUser(['SISWA'], ['username' => 'andi']);
        $this->siswa($akun, ['nisn' => '0081234561']);

        $this->postJson('/api/lupa-password', ['username' => 'andi', 'nisn' => '9999999999'])
            ->assertStatus(422)->assertJsonMissingPath('reset_url');
        $this->assertDatabaseCount('reset_password_tokens', 0);

        $this->postJson('/api/lupa-password', ['username' => 'andi', 'nisn' => '0081234561'])
            ->assertOk()->assertJsonStructure(['reset_url']);
        $this->assertDatabaseCount('reset_password_tokens', 1);
    }
}
