<?php

namespace Tests\Unit;

use App\Services\WaPenerima;
use PHPUnit\Framework\TestCase;

class WaPenerimaTest extends TestCase
{
    public function test_normalisasi_berbagai_format(): void
    {
        $this->assertSame('6281515038315', WaPenerima::normalisasiNomor('081515038315'));
        $this->assertSame('6281515038315', WaPenerima::normalisasiNomor('+6281515038315'));
        $this->assertSame('6281515038315', WaPenerima::normalisasiNomor('0815-1503-8315'));
        $this->assertSame('6281234567890', WaPenerima::normalisasiNomor('+62 812-3456-7890'));
    }

    public function test_nomor_tidak_valid_ditolak(): void
    {
        $this->assertNull(WaPenerima::normalisasiNomor(null));
        $this->assertNull(WaPenerima::normalisasiNomor(''));
        $this->assertNull(WaPenerima::normalisasiNomor('12345'));
        $this->assertNull(WaPenerima::normalisasiNomor('abc'));
        $this->assertNull(WaPenerima::normalisasiNomor('0215551234'));
    }

    public function test_pending_dan_rejected_tidak_dipilih(): void
    {
        $p = WaPenerima::pilih([
            ['hubungan' => 'Ibu',  'nama' => 'A', 'no_telepon' => '081515038315', 'status_verifikasi' => 'pending'],
            ['hubungan' => 'Ayah', 'nama' => 'B', 'no_telepon' => '081515038316', 'status_verifikasi' => 'rejected'],
        ]);

        $this->assertNull($p);
    }

    public function test_ibu_diprioritaskan_atas_ayah(): void
    {
        $p = WaPenerima::pilih([
            ['hubungan' => 'Ayah', 'nama' => 'Ayah', 'no_telepon' => '081515038316', 'status_verifikasi' => 'verified'],
            ['hubungan' => 'Ibu',  'nama' => 'Ibu',  'no_telepon' => '081515038315', 'status_verifikasi' => 'verified'],
        ]);

        $this->assertSame('Ibu', $p['hubungan']);
        $this->assertSame('6281515038315', $p['nomor']);
    }

    public function test_ayah_dipakai_kalau_ibu_tidak_punya_nomor_valid(): void
    {
        $p = WaPenerima::pilih([
            ['hubungan' => 'Ibu',  'nama' => 'Ibu',  'no_telepon' => null, 'status_verifikasi' => 'verified'],
            ['hubungan' => 'Ayah', 'nama' => 'Ayah', 'no_telepon' => '081515038316', 'status_verifikasi' => 'verified'],
        ]);

        $this->assertSame('Ayah', $p['hubungan']);
    }

    public function test_samarkan_nomor(): void
    {
        $this->assertSame('62815****8315', WaPenerima::samarkan('6281515038315'));
    }
}