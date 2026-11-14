<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\NilaiTp;
use App\Models\PaketNilai;
use App\Models\PeriodeNilai;
use App\Models\Role;
use App\Models\Rombel;
use App\Models\Semester;
use App\Models\Siswa;
use App\Models\SiswaKelas;
use App\Models\SiswaRombel;
use App\Models\TahunAjaran;
use App\Models\Tp;
use App\Models\User;
use App\Models\WaliKelas;
use App\Services\RekapNilaiService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RekapNilaiTest extends TestCase
{
    use RefreshDatabase;

    private Semester $semester;

    private Kelas $kelas;

    private Kelas $kelasLain;

    private Rombel $rombel;

    private PeriodeNilai $periode;

    private Tp $sts;

    private Tp $sas;

    private User $admin;

    private User $guru;

    private User $wali;

    private SiswaRombel $peserta;

    protected function beforeRefreshingDatabase(): void
    {
        if (! app()->environment('testing') || config('database.default') !== 'sqlite'
            || config('database.connections.sqlite.driver') !== 'sqlite'
            || config('database.connections.sqlite.database') !== ':memory:'
            || ! empty(config('database.connections.sqlite.url'))) {
            throw new \RuntimeException('STOP: pengujian DKN wajib SQLite in-memory.');
        }
    }

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.key' => 'base64:'.base64_encode(random_bytes(32))]);
        $this->seed(RoleSeeder::class);
        $tahun = TahunAjaran::create(['nama' => '2026/2027']);
        $this->semester = Semester::create(['tahun_ajaran_id' => $tahun->id, 'nama' => 'ganjil']);
        $this->kelas = Kelas::create(['semester_id' => $this->semester->id, 'nama' => 'X-1', 'tingkat' => 10, 'fase' => 'E']);
        $this->kelasLain = Kelas::create(['semester_id' => $this->semester->id, 'nama' => 'X-2', 'tingkat' => 10, 'fase' => 'E']);
        $mapel = Mapel::create(['kode' => 'BIO', 'nama' => '=1+1']);
        $this->rombel = Rombel::create(['semester_id' => $this->semester->id, 'mapel_id' => $mapel->id, 'nama' => 'Biologi gabungan']);
        $this->periode = PeriodeNilai::create(['semester_id' => $this->semester->id, 'jenis' => 'STS', 'tingkat' => 10,
            'tanggal_buka' => now()->subDay(), 'tanggal_tutup' => now()->addDay()]);
        $this->sts = Tp::create(['rombel_id' => $this->rombel->id, 'nomor_urut' => 1, 'deskripsi' => 'TP STS', 'masuk_sts' => true]);
        $this->sas = Tp::create(['rombel_id' => $this->rombel->id, 'nomor_urut' => 2, 'deskripsi' => 'TP SAS', 'is_sas' => true]);
        $this->admin = $this->akun(['admin']);
        $this->guru = $this->akun(['guru']);
        $this->wali = $this->akun(['wali_kelas']);
        $this->rombel->gurus()->attach($this->guru->guru->id);
        WaliKelas::create(['kelas_id' => $this->kelas->id, 'guru_id' => $this->wali->guru->id]);
        $this->peserta = $this->peserta($this->kelas, '00123', '=SUM(1,2)');
        $this->peserta($this->kelasLain, '00124', 'Siswa Dua');
    }

    private function akun(array $roles): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->roles()->attach(Role::whereIn('name', $roles)->pluck('id'));
        if (array_intersect($roles, ['guru', 'wali_kelas'])) {
            $guru = new Guru(['nama' => 'Guru Uji', 'status_kepegawaian' => 'ASN', 'aktif' => true]);
            $guru->forceFill(['user_id' => $user->id])->save();
        }

        return $user;
    }

    private function peserta(Kelas $kelas, string $nis, string $nama, ?Rombel $rombel = null): SiswaRombel
    {
        $siswa = Siswa::create(['nis' => $nis, 'nama' => $nama, 'aktif' => true]);
        $sk = SiswaKelas::create(['siswa_id' => $siswa->id, 'kelas_id' => $kelas->id, 'semester_id' => $kelas->semester_id]);

        return SiswaRombel::create(['rombel_id' => ($rombel ?? $this->rombel)->id, 'siswa_kelas_id' => $sk->id]);
    }

    private function url(string $suffix = '', array $filters = []): string
    {
        return '/api/rekap-nilai'.$suffix.'?'.http_build_query(['periode_id' => $this->periode->id] + $filters);
    }

    public function test_admin_kurikulum_dan_guru_pengampu_melihat_semua_peserta_rombel(): void
    {
        foreach ([$this->admin, $this->akun(['kurikulum']), $this->guru] as $user) {
            $this->actingAs($user)->getJson($this->url())->assertOk()->assertJsonPath('meta.total', 2);
            $this->getJson($this->url('/rombel/'.$this->rombel->id))->assertOk()
                ->assertJsonCount(1, 'meta.tp')->assertJsonCount(2, 'data');
        }
    }

    public function test_wali_hanya_melihat_kelas_sendiri_dalam_rombel_gabungan(): void
    {
        PaketNilai::create(['rombel_id' => $this->rombel->id, 'periode_id' => $this->periode->id, 'kelas_id' => $this->kelasLain->id, 'status' => 'dikunci']);
        foreach (['', '/rombel/'.$this->rombel->id] as $suffix) {
            $this->actingAs($this->wali)->getJson($this->url($suffix))->assertOk()->assertJsonCount(1, 'data')
                ->assertJsonPath('data.0.kelas.id', $this->kelas->id)->assertJsonPath('data.0.status_paket', null);
        }
        $this->getJson($this->url('', ['kelas_id' => $this->kelasLain->id]))->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_multi_role_menggabungkan_hak_pengampu_dan_wali(): void
    {
        $this->wali->roles()->attach(Role::where('name', 'guru')->firstOrFail()->id);
        $this->rombel->gurus()->attach($this->wali->guru->id);
        $this->actingAs($this->wali)->getJson($this->url())->assertOk()->assertJsonPath('meta.total', 2);
    }

    public function test_akses_tanpa_login_role_terlarang_dan_guru_lain_dibatasi(): void
    {
        $this->getJson($this->url())->assertUnauthorized();
        foreach (['siswa', 'wali_siswa'] as $role) {
            $this->actingAs($this->akun([$role]))->getJson($this->url())->assertForbidden();
        }
        $this->actingAs($this->akun(['guru']))->getJson($this->url())->assertOk()->assertJsonCount(0, 'data');
        $this->getJson($this->url('/rombel/'.$this->rombel->id))->assertForbidden();
    }

    public function test_semester_dan_tingkat_disaring_dan_detail_beda_semester_ditolak(): void
    {
        $xi = Kelas::create(['semester_id' => $this->semester->id, 'nama' => 'XI-1', 'tingkat' => 11, 'fase' => 'F']);
        $this->peserta($xi, '00125', 'Siswa XI');
        $semester = Semester::create(['tahun_ajaran_id' => $this->semester->tahun_ajaran_id, 'nama' => 'genap']);
        $rombel = Rombel::create(['semester_id' => $semester->id, 'mapel_id' => $this->rombel->mapel_id, 'nama' => 'Genap']);
        $kelas = Kelas::create(['semester_id' => $semester->id, 'nama' => 'X-1', 'tingkat' => 10, 'fase' => 'E']);
        $this->peserta($kelas, '00126', 'Siswa Genap', $rombel);
        $this->actingAs($this->admin)->getJson($this->url())->assertOk()->assertJsonPath('meta.total', 2);
        $this->getJson($this->url('/rombel/'.$rombel->id))->assertUnprocessable();
        $this->periode->update(['tingkat' => 11]);
        $this->getJson($this->url('/rombel/'.$this->rombel->id))->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.siswa.nama', 'Siswa XI');
    }

    public function test_sts_sas_nol_null_dan_peserta_tanpa_record(): void
    {
        NilaiTp::create(['tp_id' => $this->sts->id, 'siswa_rombel_id' => $this->peserta->id, 'skor' => 0]);
        NilaiTp::create(['tp_id' => $this->sas->id, 'siswa_rombel_id' => $this->peserta->id, 'skor' => null]);
        $this->actingAs($this->admin)->getJson($this->url())->assertOk()
            ->assertJsonPath('data.0.nilai_tp.0.skor', '0.00')->assertJsonPath('data.0.jumlah_tp_terisi', 1)
            ->assertJsonPath('data.0.lengkap', true)->assertJsonPath('data.1.nilai_tp.0.skor', null)
            ->assertJsonPath('data.1.lengkap', false)->assertJsonPath('data.0.nilai_akhir', '0.000000')
            ->assertJsonPath('data.0.alasan_nilai_akhir', null)
            ->assertJsonPath('data.1.alasan_nilai_akhir', 'nilai_belum_lengkap');
        $this->periode->update(['jenis' => 'SAS']);
        $this->getJson($this->url('/rombel/'.$this->rombel->id))->assertOk()
            ->assertJsonCount(2, 'meta.tp')->assertJsonPath('meta.tp.1.id', $this->sas->id)
            ->assertJsonPath('data.0.jumlah_tp_terisi', 1)->assertJsonPath('data.0.nilai_tp.1.skor', null)
            ->assertJsonPath('data.0.nilai_akhir', null)->assertJsonPath('data.0.alasan_nilai_akhir', 'nilai_belum_lengkap');
    }

    public function test_rombel_tanpa_tp_dan_status_paket_per_kelas(): void
    {
        $this->rombel->tp()->delete();
        foreach ([$this->kelas->id => 'dikirim', $this->kelasLain->id => 'perlu_perbaikan'] as $id => $status) {
            PaketNilai::create(['rombel_id' => $this->rombel->id, 'periode_id' => $this->periode->id, 'kelas_id' => $id, 'status' => $status]);
        }
        $this->actingAs($this->admin)->getJson($this->url('/rombel/'.$this->rombel->id))->assertOk()
            ->assertJsonCount(0, 'meta.tp')->assertJsonPath('data.0.jumlah_tp', 0)
            ->assertJsonPath('data.0.lengkap', false)->assertJsonPath('data.0.status_paket', 'dikirim')
            ->assertJsonPath('data.1.status_paket', 'perlu_perbaikan')
            ->assertJsonPath('data.0.nilai_akhir', null)->assertJsonPath('data.0.alasan_nilai_akhir', 'tidak_ada_tp');
    }

    public function test_pagination_filter_dan_validasi_request(): void
    {
        $this->actingAs($this->admin)->getJson($this->url('', ['per_page' => 1, 'page' => 2]))->assertOk()
            ->assertJsonPath('meta.total', 2)->assertJsonCount(1, 'data')->assertJsonPath('meta.current_page', 2);
        $this->getJson($this->url('', ['kelas_id' => $this->kelas->id, 'rombel_id' => $this->rombel->id]))->assertOk()->assertJsonPath('meta.total', 1);
        $this->getJson('/api/rekap-nilai')->assertUnprocessable()->assertJsonValidationErrors('periode_id');
        $this->getJson($this->url('', ['per_page' => 101]))->assertUnprocessable();
    }

    public function test_export_dibatasi_admin_kurikulum_dan_scope_wajib(): void
    {
        foreach ([$this->guru, $this->wali, $this->akun(['siswa'])] as $user) {
            $this->actingAs($user)->getJson($this->url('/export', ['scope' => 'semua']))->assertForbidden();
        }
        $this->actingAs($this->admin)->getJson($this->url('/export'))->assertUnprocessable()->assertJsonValidationErrors('scope');
    }

    public function test_excel_memuat_semua_baris_konsisten_dengan_json_dan_aman(): void
    {
        NilaiTp::create(['tp_id' => $this->sts->id, 'siswa_rombel_id' => $this->peserta->id, 'skor' => 0]);
        $this->actingAs($this->admin);
        $json = $this->getJson($this->url())->assertOk()->json('data');
        $response = $this->get($this->url('/export', ['scope' => 'semua', 'per_page' => 1, 'page' => 2]))->assertOk();
        $path = $response->baseResponse->getFile()->getPathname();
        try {
            $book = IOFactory::load($path);
            $sheet = $book->getActiveSheet();
            $this->assertSame(3, $sheet->getHighestRow());
            foreach ($json as $i => $row) {
                $r = $i + 2;
                $this->assertSame($row['siswa']['nis'], $sheet->getCell('B'.$r)->getValue());
                $this->assertSame($row['siswa']['nama'], $sheet->getCell('C'.$r)->getValue());
                $this->assertEquals($row['jumlah_tp_terisi'], $sheet->getCell('R'.$r)->getValue());
                if ($row['nilai_akhir'] === null) {
                    $this->assertNull($sheet->getCell('U'.$r)->getValue());
                } else {
                    $this->assertEquals((float) $row['nilai_akhir'], $sheet->getCell('U'.$r)->getValue());
                }
                $this->assertSame($row['alasan_nilai_akhir'], $sheet->getCell('V'.$r)->getValue());
            }
            $this->assertSame('00123', $sheet->getCell('B2')->getValue());
            $this->assertSame(DataType::TYPE_STRING, $sheet->getCell('B2')->getDataType());
            $this->assertSame('=SUM(1,2)', $sheet->getCell('C2')->getValue());
            $this->assertSame(DataType::TYPE_STRING, $sheet->getCell('C2')->getDataType());
            $this->assertSame(DataType::TYPE_STRING, $sheet->getCell('F2')->getDataType());
            $this->assertEquals(0, $sheet->getCell('G2')->getValue());
            $this->assertNull($sheet->getCell('G3')->getValue());
            $book->disconnectWorksheets();
        } finally {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    public function test_jumlah_query_baca_tidak_bertambah_per_peserta(): void
    {
        $this->actingAs($this->admin);
        $this->getJson($this->url())->assertOk();
        DB::enableQueryLog();
        DB::flushQueryLog();
        $this->getJson($this->url())->assertOk();
        $count = count(DB::getQueryLog());
        DB::disableQueryLog();
        for ($i = 0; $i < 5; $i++) {
            $this->peserta($this->kelas, 'extra'.$i, 'Siswa '.$i);
        }
        DB::enableQueryLog();
        DB::flushQueryLog();
        $this->getJson($this->url())->assertOk()->assertJsonPath('meta.total', 7);
        $this->assertSame($count, count(DB::getQueryLog()));
        DB::disableQueryLog();
    }

    public static function variasiTp(): array
    {
        return [
            '4 TP' => [4, '85.000000'],
            '6 TP' => [6, '76.666666'],
            '8 TP' => [8, '72.500000'],
            '9 TP' => [9, '71.111111'],
            '10 TP' => [10, '70.000000'],
        ];
    }

    #[DataProvider('variasiTp')]
    public function test_rumus_sts_dan_sas_memakai_jumlah_tp_aktual(int $jumlah, string $sas): void
    {
        $this->rombel->tp()->delete();
        for ($i = 1; $i <= $jumlah; $i++) {
            $tp = Tp::create(['rombel_id' => $this->rombel->id, 'nomor_urut' => $i,
                'deskripsi' => 'TP '.$i, 'masuk_sts' => $i <= 2, 'is_sas' => $i === $jumlah]);
            NilaiTp::create(['tp_id' => $tp->id, 'siswa_rombel_id' => $this->peserta->id,
                'skor' => [1 => 80, 2 => 90, 3 => 70, 4 => 100][$i] ?? 60]);
        }
        $this->actingAs($this->admin);
        foreach (['STS' => ['85.000000', 2], 'SAS' => [$sas, $jumlah]] as $jenis => [$expected, $count]) {
            $this->periode->update(['jenis' => $jenis]);
            foreach (['', '/rombel/'.$this->rombel->id] as $suffix) {
                $response = $this->getJson($this->url($suffix))->assertOk()
                    ->assertJsonPath('data.0.jumlah_tp', $count)->assertJsonPath('data.0.jumlah_tp_terisi', $count)
                    ->assertJsonPath('data.0.nilai_akhir', $expected)->assertJsonPath('data.0.alasan_nilai_akhir', null);
                if ($suffix !== '') {
                    $response->assertJsonCount($count, 'meta.tp');
                }
            }
        }
        // SAS: bandingkan hasil akhir JSON dengan file Excel sebenarnya.
        $response = $this->get($this->url('/export', ['scope' => 'semua']))->assertOk();
        $path = $response->baseResponse->getFile()->getPathname();
        try {
            $book = IOFactory::load($path);
            $this->assertEquals((float) $sas, $book->getActiveSheet()->getCell('U2')->getValue());
            $this->assertSame(DataType::TYPE_NUMERIC, $book->getActiveSheet()->getCell('U2')->getDataType());
            $this->assertSame('0.000000', $book->getActiveSheet()->getStyle('U2')->getNumberFormat()->getFormatCode());
            $book->disconnectWorksheets();
        } finally {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    public function test_presisi_skor_desimal_dan_sts_tanpa_tp_relevan(): void
    {
        $this->sas->update(['masuk_sts' => true]);
        NilaiTp::create(['tp_id' => $this->sts->id, 'siswa_rombel_id' => $this->peserta->id, 'skor' => '80.01']);
        NilaiTp::create(['tp_id' => $this->sas->id, 'siswa_rombel_id' => $this->peserta->id, 'skor' => '90.02']);
        $this->actingAs($this->admin)->getJson($this->url())->assertOk()->assertJsonPath('data.0.nilai_akhir', '85.015000');
        $this->rombel->tp()->update(['masuk_sts' => false]);
        $this->getJson($this->url())->assertOk()->assertJsonPath('data.0.nilai_akhir', null)
            ->assertJsonPath('data.0.alasan_nilai_akhir', 'tidak_ada_tp');
    }

    public function test_nilai_akhir_tidak_mencampur_tp_rombel_dan_semester_lain(): void
    {
        NilaiTp::create(['tp_id' => $this->sts->id, 'siswa_rombel_id' => $this->peserta->id, 'skor' => 80]);
        $genap = Semester::create(['tahun_ajaran_id' => $this->semester->tahun_ajaran_id, 'nama' => 'genap']);
        foreach ([$this->semester->id, $genap->id] as $semesterId) {
            $rombel = Rombel::create(['semester_id' => $semesterId, 'mapel_id' => $this->rombel->mapel_id, 'nama' => 'Rombel lain']);
            $tp = Tp::create(['rombel_id' => $rombel->id, 'nomor_urut' => 1, 'deskripsi' => 'TP lain', 'masuk_sts' => true]);
            // Bahkan baris tidak konsisten yang bisa dibuat langsung di DB tidak boleh ikut rekap.
            NilaiTp::create(['tp_id' => $tp->id, 'siswa_rombel_id' => $this->peserta->id, 'skor' => 100]);
        }
        $this->actingAs($this->admin)->getJson($this->url())->assertOk()
            ->assertJsonPath('data.0.jumlah_tp', 1)->assertJsonPath('data.0.nilai_akhir', '80.000000');
    }

    public static function pembagianTp(): array
    {
        return [
            '1 TP' => [1, 0, 1], '2 TP' => [2, 1, 1],
            '3 TP' => [3, 1, 2], '4 TP' => [4, 2, 2],
            '5 TP' => [5, 2, 3], '6 TP' => [6, 3, 3],
            '7 TP' => [7, 3, 4], '8 TP' => [8, 4, 4],
            '9 TP' => [9, 4, 5], '10 TP' => [10, 5, 5],
        ];
    }

    #[DataProvider('pembagianTp')]
    public function test_dkn_membaca_alokasi_1_sampai_10_tp_yang_sudah_sesuai(int $total, int $sts, int $sas): void
    {
        // Ini spesifikasi alokasi dan fixture DKN, bukan bukti controller TP
        // sudah mengalokasikan otomatis. Seluruh data hanya di SQLite RAM.
        $this->assertSame($sts, intdiv($total, 2));
        $this->assertSame($sas, $total - $sts);
        $this->rombel->tp()->delete();
        // Buat dengan urutan ID terbalik untuk membuktikan pembacaan nomor_urut.
        for ($nomor = $total; $nomor >= 1; $nomor--) {
            $tp = Tp::create(['rombel_id' => $this->rombel->id, 'nomor_urut' => $nomor,
                'deskripsi' => 'Alokasi TP '.$nomor, 'masuk_sts' => $nomor <= $sts, 'is_sas' => $nomor > $sts]);
            NilaiTp::create(['tp_id' => $tp->id, 'siswa_rombel_id' => $this->peserta->id, 'skor' => $nomor * 10]);
        }
        $tp = $this->rombel->tp()->get();
        $this->assertSame(range(1, $total), $tp->pluck('nomor_urut')->all());
        $this->assertSame($sts === 0 ? [] : range(1, $sts), $tp->where('masuk_sts', true)->pluck('nomor_urut')->values()->all());
        $this->assertSame(range($sts + 1, $total), $tp->where('is_sas', true)->pluck('nomor_urut')->values()->all());
        $this->actingAs($this->admin);
        foreach (['STS', 'SAS'] as $jenis) {
            $this->periode->update(['jenis' => $jenis]);
            $count = $jenis === 'STS' ? $sts : $total;
            $expected = $count === 0 ? null : number_format(($count + 1) * 5, 6, '.', '');
            foreach (['', '/rombel/'.$this->rombel->id] as $suffix) {
                $response = $this->getJson($this->url($suffix))->assertOk()
                    ->assertJsonPath('data.0.jumlah_tp', $count)
                    ->assertJsonPath('data.0.nilai_akhir', $expected)
                    ->assertJsonPath('data.0.alasan_nilai_akhir', $count === 0 ? 'tidak_ada_tp' : null);
                $this->assertSame($count === 0 ? [] : range(1, $count), array_column($response->json('data.0.nilai_tp'), 'nomor_urut'));
            }
        }
    }

    public function test_contoh_lima_tp_sts_85_sas_84(): void
    {
        $this->rombel->tp()->delete();
        foreach ([80, 90, 70, 85, 95] as $index => $skor) {
            $tp = Tp::create(['rombel_id' => $this->rombel->id, 'nomor_urut' => $index + 1,
                'deskripsi' => 'Contoh TP', 'masuk_sts' => $index < 2, 'is_sas' => $index >= 2]);
            NilaiTp::create(['tp_id' => $tp->id, 'siswa_rombel_id' => $this->peserta->id, 'skor' => $skor]);
        }
        $this->actingAs($this->admin)->getJson($this->url())->assertOk()->assertJsonPath('data.0.nilai_akhir', '85.000000');
        $this->periode->update(['jenis' => 'SAS']);
        $this->getJson($this->url())->assertOk()->assertJsonPath('data.0.nilai_akhir', '84.000000');
    }

    public function test_export_menolak_nomor_tp_di_luar_rentang(): void
    {
        $this->actingAs($this->admin);
        foreach ([0, 11] as $nomor) {
            $this->sts->update(['nomor_urut' => $nomor]);
            $this->getJson($this->url('/export', ['scope' => 'semua']))
                ->assertUnprocessable()->assertJsonValidationErrors('tp')
                ->assertJsonPath('errors.tp.0', 'Ekspor dibatalkan: nomor TP pada rombel '.$this->rombel->id.' harus unik dan berada pada rentang 1–10. Periksa data TP.');
        }
    }

    public function test_export_menolak_nomor_duplikat_sebelum_membuat_excel(): void
    {
        // Unique DB mencegah duplikat pada skema normal. Simulasikan keluaran
        // service tidak valid tanpa mengubah constraint/migration database.
        $this->actingAs($this->admin);
        $rows = collect($this->getJson($this->url())->assertOk()->json('data'));
        $rows = $rows->map(function ($row) {
            $row['nilai_tp'][] = $row['nilai_tp'][0];

            return $row;
        });
        $this->partialMock(RekapNilaiService::class, function ($mock) use ($rows) {
            $mock->shouldReceive('rows')->once()->andReturn($rows);
        });
        $this->getJson($this->url('/export', ['scope' => 'semua']))->assertUnprocessable()->assertJsonValidationErrors('tp');
    }

    private function rombelLainDenganDuaKelas(): array
    {
        $rombel = Rombel::create(['semester_id' => $this->semester->id, 'mapel_id' => $this->rombel->mapel_id, 'nama' => 'Rombel B']);
        $tp = Tp::create(['rombel_id' => $rombel->id, 'nomor_urut' => 1, 'deskripsi' => 'TP B', 'masuk_sts' => true]);
        $satu = $this->peserta($this->kelas, '00801', 'Peserta B kelas 1', $rombel);
        $dua = $this->peserta($this->kelasLain, '00802', 'Peserta B kelas 2', $rombel);
        foreach ([$satu, $dua] as $p) {
            NilaiTp::create(['tp_id' => $tp->id, 'siswa_rombel_id' => $p->id, 'skor' => 99]);
        }

        return [$rombel, $satu, $dua];
    }

    public function test_guru_rombel_a_tidak_mendapat_peserta_dan_nilai_rombel_b(): void
    {
        [$rombel] = $this->rombelLainDenganDuaKelas();
        $this->actingAs($this->guru)->getJson($this->url())->assertOk()->assertJsonPath('meta.total', 2)
            ->assertJsonMissing(['nama' => 'Peserta B kelas 1'])->assertJsonMissing(['nama' => 'Peserta B kelas 2']);
        $this->getJson($this->url('', ['rombel_id' => $rombel->id]))->assertOk()->assertJsonCount(0, 'data');
        $this->getJson($this->url('/rombel/'.$rombel->id))->assertForbidden();
    }

    public function test_multi_role_guru_a_dan_wali_sebagian_b_hanya_gabungan_sah(): void
    {
        [$rombel, $satu, $dua] = $this->rombelLainDenganDuaKelas();
        $this->wali->roles()->attach(Role::where('name', 'guru')->firstOrFail()->id);
        $this->rombel->gurus()->attach($this->wali->guru->id);
        $response = $this->actingAs($this->wali)->getJson($this->url())->assertOk()->assertJsonPath('meta.total', 3);
        $ids = array_column($response->json('data'), 'siswa_rombel_id');
        $this->assertContains($satu->id, $ids);
        $this->assertNotContains($dua->id, $ids);
        $this->getJson($this->url('/rombel/'.$rombel->id))->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.siswa_rombel_id', $satu->id)->assertJsonPath('data.0.nilai_tp.0.skor', '99.00');
        $this->getJson($this->url('', ['rombel_id' => $rombel->id, 'kelas_id' => $this->kelasLain->id]))
            ->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_kurikulum_export_mematuhi_filter_kelas_dan_rombel(): void
    {
        [$rombel] = $this->rombelLainDenganDuaKelas();
        $this->actingAs($this->akun(['kurikulum']));
        foreach ([['kelas_id' => $this->kelas->id], ['rombel_id' => $rombel->id],
            ['kelas_id' => $this->kelas->id, 'rombel_id' => $rombel->id]] as $filters) {
            $json = $this->getJson($this->url('', $filters))->assertOk()->json('data');
            $response = $this->get($this->url('/export', $filters + ['scope' => 'semua', 'per_page' => 1]))->assertOk();
            $path = $response->baseResponse->getFile()->getPathname();
            try {
                $book = IOFactory::load($path);
                $sheet = $book->getActiveSheet();
                $this->assertSame(count($json) + 1, $sheet->getHighestRow());
                foreach ($json as $i => $row) {
                    $this->assertEquals($row['siswa_rombel_id'], $sheet->getCell('A'.($i + 2))->getValue());
                    $this->assertSame($row['kelas']['nama'], $sheet->getCell('D'.($i + 2))->getValue());
                    $this->assertSame($row['rombel']['nama'], $sheet->getCell('E'.($i + 2))->getValue());
                }
                $book->disconnectWorksheets();
            } finally {
                if (is_file($path)) {
                    unlink($path);
                }
            }
        }
    }
}
