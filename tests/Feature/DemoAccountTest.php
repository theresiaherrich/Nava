<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\WebNavigation;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoAccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Setiap akun demo (DemoAccountSeeder) bisa login dan langsung masuk ke dashboard perannya. */
class DemoAccountTest extends TestCase
{
    use RefreshDatabase;

    private const SANDI = 'Sandi-Demo-Uji-123';

    /** username => [halaman pertama, menu sidebar yang wajib ada, endpoint API dashboard/beranda]. */
    private const HARAPAN = [
        'superadmin' => ['/superadmin/dashboard', ['Dashboard Super Admin'], []],
        'operator' => ['/operator/dashboard', ['Dashboard Operator', 'Data Master'], ['/api/master/dashboard']],
        'kurikulum' => ['/kurikulum/dashboard', ['Dashboard Kurikulum', 'Periode dan Penguncian', 'Rekap Nilai'], ['/api/kurikulum/dashboard', '/api/kurikulum/periode']],
        'kesiswaan' => ['/kesiswaan/dashboard', ['Dashboard Kesiswaan', 'Predikat Ekstrakurikuler'], ['/api/kesiswaan/dashboard']],
        'guru_mapel' => ['/guru/dashboard', ['Dashboard Guru', 'Kelola TP', 'Input Nilai'], ['/api/guru/dashboard']],
        'wali_kelas' => ['/wali-kelas/dashboard', ['Dashboard Kelas', 'Verifikasi Nilai', 'Cetak dan Arsip Rapor', 'Masuk sebagai Guru Mapel'], ['/api/wali/dashboard', '/api/wali/periode']],
        'guru_bk' => ['/bk/dashboard', ['Dashboard Guru BK'], []],
        'siswa_demo' => ['/siswa/beranda', ['Beranda', 'Nilai per Mapel', 'Rapor Saya'], ['/api/portal/beranda', '/api/portal/nilai']],
        'ortu_demo' => ['/orang-tua/beranda', ['Nilai Anak', 'Rapor Anak'], ['/api/portal/anak']],
    ];

    protected function setUp(): void
    {
        parent::setUp();
        config(['siakad.demo_password' => self::SANDI, 'siakad.seed_default_password' => null]);
        // DemoAccountSeeder hanya berjalan di APP_ENV=local; request uji tetap di env testing.
        $this->sebagaiEnv('local', fn () => $this->seed(DatabaseSeeder::class));
    }

    private function sebagaiEnv(string $env, callable $callback): void
    {
        $this->app->detectEnvironment(fn () => $env);
        try {
            $callback();
        } finally {
            $this->app->detectEnvironment(fn () => 'testing');
        }
    }

    private function jalankanSeederDemo(): void
    {
        $this->app->make(DemoAccountSeeder::class)->setContainer($this->app)->__invoke();
    }

    public function test_setiap_akun_demo_login_ke_dashboard_perannya_tanpa_403_atau_404(): void
    {
        $this->assertSame(array_keys(DemoAccountSeeder::AKUN), array_keys(self::HARAPAN));

        foreach (self::HARAPAN as $username => [$beranda, $menu, $api]) {
            $this->postJson('/api/auth/login', ['username' => $username, 'password' => self::SANDI])
                ->assertOk()->assertJsonPath('data.must_change_password', false);

            $this->get('/dashboard')->assertRedirect($beranda);

            $halaman = $this->get($beranda)->assertOk();
            foreach ($menu as $label) {
                $halaman->assertSee($label, false);
            }

            // Semua menu Akademik peran ini (yang tampil di sidebar) bisa dibuka.
            foreach (array_keys(WebNavigation::links()) as $url) {
                $halaman->assertSee(url($url), false);
                $this->get($url)->assertOk();
            }

            foreach ($api as $endpoint) {
                $this->getJson($endpoint)->assertOk();
            }

            $this->postJson('/api/auth/logout')->assertOk();
            $this->app['auth']->forgetGuards();
        }
    }

    public function test_wali_kelas_demo_memegang_x_b_dan_siswa_demo_anggotanya(): void
    {
        $this->postJson('/api/auth/login', ['username' => 'wali_kelas', 'password' => self::SANDI])->assertOk();
        $this->getJson('/api/wali/dashboard')->assertOk()->assertSee('X-B');

        $siswa = User::where('username', 'siswa_demo')->firstOrFail()->siswa;
        $this->assertSame('X-B', $siswa->riwayatKelas()->with('kelas')->first()->kelas->nama);
        $this->assertSame([$siswa->id_siswa], User::where('username', 'ortu_demo')->firstOrFail()->orangTua->anak->pluck('id_siswa')->all());
    }

    public function test_seeder_aman_dijalankan_ulang_dan_hanya_di_local(): void
    {
        $this->sebagaiEnv('local', fn () => $this->jalankanSeederDemo());
        $this->assertSame(1, User::where('username', 'wali_kelas')->count());
        $this->assertSame(1, \App\Models\Siswa::where('nis', 'DEMO-001')->count());

        User::where('username', 'operator')->delete();
        foreach (['production', 'testing'] as $env) {
            $this->sebagaiEnv($env, fn () => $this->jalankanSeederDemo());
            $this->assertFalse(User::where('username', 'operator')->exists(), "Akun demo tidak boleh dibuat di {$env}.");
        }
    }
}
