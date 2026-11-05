<?php

namespace Tests\Feature;

use Tests\TestCase;

class TpTest extends TestCase
{
    public function test_tamu_tidak_bisa_melihat_tp(): void
    {
        $response = $this->getJson('/api/rombel/1/tp');

        $response->assertStatus(401);
    }

    public function test_tamu_tidak_bisa_menambah_tp(): void
    {
        $response = $this->postJson('/api/rombel/1/tp', [
            'kode' => 'TP01',
            'deskripsi' => 'Memahami persamaan linear',
            'masuk_sts' => true,
            'is_sas' => false,
        ]);

        $response->assertStatus(401);
    }

    public function test_tamu_tidak_bisa_mengubah_tp(): void
    {
        $response = $this->putJson('/api/tp/1', [
            'deskripsi' => 'Menyelesaikan persamaan linear',
        ]);

        $response->assertStatus(401);
    }

    public function test_tamu_tidak_bisa_menghapus_tp(): void
    {
        $response = $this->deleteJson('/api/tp/1');

        $response->assertStatus(401);
    }

    public function test_tamu_tidak_bisa_menyalin_tp(): void
    {
        $response = $this->postJson('/api/tp/salin', [
            'rombel_asal' => 1,
            'rombel_tujuan' => 2,
        ]);

        $response->assertStatus(401);
    }
}