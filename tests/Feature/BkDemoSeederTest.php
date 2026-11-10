<?php

namespace Tests\Feature;

use Database\Seeders\BkDemoSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BkDemoSeederTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_demo_seeder_populates_the_nava_schema(): void
    {
        $this->seed(BkDemoSeeder::class);

        $idGuru = DB::table('guru')->where('nip', '198503152010012003')->value('id_guru');
        $idTahunAjaran = DB::table('tahun_ajaran')->where('nama', '2025/2026')->value('id_tahun_ajaran');
        $idSemester = DB::table('semester')
            ->where('id_tahun_ajaran', $idTahunAjaran)
            ->where('nama', 'GASAL')
            ->value('id_semester');
        $idSiswa = DB::table('siswa')->where('nis', '2410073')->value('id_siswa');
        $idRiwayatKelas = DB::table('riwayat_kelas')
            ->where('id_siswa', $idSiswa)
            ->where('id_semester', $idSemester)
            ->value('id_riwayat_kelas');

        $this->assertDatabaseHas('user', [
            'username' => 'rina.wulandari',
            'role' => 'GURU',
        ]);
        $this->assertDatabaseHas('guru_bk', [
            'id_guru' => $idGuru,
            'aktif' => true,
        ]);
        $this->assertDatabaseHas('kelas', [
            'nama' => 'X-3',
            'id_tahun_ajaran' => $idTahunAjaran,
        ]);
        $this->assertDatabaseHas('riwayat_kelas', [
            'id_riwayat_kelas' => $idRiwayatKelas,
            'id_kelas' => DB::table('kelas')->where('nama', 'X-3')->value('id_kelas'),
        ]);
        $this->assertDatabaseHas('periode_tes', [
            'id_semester' => $idSemester,
            'tingkat' => 'X',
            'status' => 'aktif',
        ]);
        $this->assertDatabaseHas('sesi_tes', [
            'id_riwayat_kelas' => $idRiwayatKelas,
            'kode_holland' => 'IAS',
            'skor_i' => 91,
        ]);
        $this->assertDatabaseHas('pengajuan_bk', [
            'topik' => 'Konsultasi pilihan jurusan',
            'status' => 'menunggu',
        ]);
        $this->assertDatabaseHas('pengajuan_bk', [
            'topik' => 'Perencanaan studi lanjut',
            'status' => 'dijadwalkan',
            'balasan_bk' => 'Sesi bimbingan telah dijadwalkan.',
        ]);
        $this->assertDatabaseHas('semester', [
            'id_semester' => DB::table('semester')
                ->where('id_tahun_ajaran', DB::table('tahun_ajaran')->where('nama', '2026/2027')->value('id_tahun_ajaran'))
                ->where('nama', 'GASAL')
                ->value('id_semester'),
            'aktif' => true,
        ]);
    }

    public function test_demo_accounts_can_log_in_and_render_their_dashboards(): void
    {
        $this->seed(BkDemoSeeder::class);
        DB::statement("
            CREATE VIEW v_rekap_tes_bk AS
            SELECT rk.id_semester,
                CASE WHEN st.id_sesi_tes IS NULL THEN 'Belum' ELSE 'Selesai' END AS status
            FROM riwayat_kelas rk
            LEFT JOIN sesi_tes st ON st.id_riwayat_kelas = rk.id_riwayat_kelas
        ");

        $this->post(route('login.post'), [
            'username' => 'nadia',
            'password' => 'password123',
        ])->assertRedirect(route('siswa.dashboard'));
        $this->get(route('siswa.dashboard'))->assertOk();

        $this->post(route('login.post'), [
            'username' => 'rina.wulandari',
            'password' => 'password123',
        ])->assertRedirect(route('bk.dashboard'));
        $this->get(route('bk.dashboard'))->assertOk();
    }
}
