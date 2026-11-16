<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Auth\GenericUser;

class RombelTest extends TestCase
{
    public function test_tamu_tidak_bisa_melihat_rombel(): void
    {
        $response = $this->getJson('/api/rombel?semester_id=1');

        $response->assertStatus(401);
    }

    public function test_tamu_tidak_bisa_menambah_rombel(): void
    {
        $response = $this->postJson('/api/rombel', [
            'id_semester' => 1,
            'id_mapel' => 1,
            'nama' => 'Matematika X-1',
            'tingkat' => 'X',
        ]);

        $response->assertStatus(401);
    }

    public function test_siswa_tidak_bisa_menambah_rombel(): void
    {
        // 1. Membuat user sementara untuk pengujian
        $user = new GenericUser([
            'id' => 999,
            'role' => 'SISWA',
        ]);

        // 2. Simulasikan user tersebut sudah login
        $this->actingAs($user);

        // 3. Kirim request POST ke endpoint
        $response = $this->postJson('/api/rombel', [
            'id_semester' => 1,
            'id_mapel' => 1,
            'nama' => 'Matematika X-1',
            'tingkat' => 'X',
        ]);

        // 4. Pastikan akses ditolak
        $response->assertStatus(403);
    }
}