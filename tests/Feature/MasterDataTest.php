<?php

namespace Tests\Feature;

use App\Models\Agama;
use App\Models\Mapel;
use App\Models\MutasiKelas;
use App\Models\Rombel;
use App\Models\Semester;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\Support\SiakadFixtures;
use Tests\TestCase;

class MasterDataTest extends TestCase
{
    use RefreshDatabase, SiakadFixtures;

    private User $operator;

    private User $kurikulum;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReferensi();
        $this->operator = $this->buatUser(['ADMIN']);
        $this->kurikulum = $this->buatUser(['KURIKULUM']);
        $this->semesterAktif();
    }

    public function test_operator_crud_siswa_dan_kurikulum_hanya_membaca(): void
    {
        $this->sebagai($this->operator);
        $id = $this->postJson('/api/master/siswa', ['nis' => '2001', 'nisn' => '0012345678', 'nama' => 'Dewi'])
            ->assertCreated()->assertJsonPath('data.nisn', '0012345678')->json('data.id_siswa');
        $this->postJson('/api/master/siswa', ['nis' => '2002', 'nisn' => '123', 'nama' => 'X'])->assertStatus(422)->assertJsonValidationErrors('nisn');
        $this->putJson("/api/master/siswa/{$id}", ['nama' => 'Dewi Lestari'])->assertOk()->assertJsonPath('data.nama', 'Dewi Lestari');
        $this->getJson('/api/master/siswa?q=lestari')->assertOk()->assertJsonPath('meta.total', 1);

        $this->sebagai($this->kurikulum);
        $this->getJson('/api/master/siswa')->assertOk();
        $this->postJson('/api/master/siswa', ['nis' => '2003', 'nama' => 'Y'])->assertForbidden();
    }

    public function test_nip_wajib_untuk_asn_dan_boleh_kosong_untuk_gtt(): void
    {
        $this->sebagai($this->operator);
        $this->postJson('/api/master/guru', ['nama' => 'A', 'status_kepegawaian' => 'ASN'])->assertStatus(422)->assertJsonValidationErrors('nip');
        $this->postJson('/api/master/guru', ['nama' => 'B', 'status_kepegawaian' => 'GTT'])->assertCreated()->assertJsonPath('data.nip', null);
    }

    public function test_anggota_kelas_satu_kelas_per_semester_dan_pindah_tercatat(): void
    {
        $xa = $this->kelas('X-A');
        $xb = $this->kelas('X-B');
        $xia = $this->kelas('XI-A', 'XI');
        $s = $this->siswa();
        $this->sebagai($this->operator);

        $this->postJson("/api/master/kelas/{$xa->id_kelas}/anggota", ['id_siswa' => [$s->id_siswa]])->assertOk()->assertJsonPath('data.ditambahkan', 1);
        $this->postJson("/api/master/kelas/{$xb->id_kelas}/anggota", ['id_siswa' => [$s->id_siswa]])->assertOk()->assertJsonPath('data.ditambahkan', 0);

        $idAnggota = $this->getJson("/api/master/kelas/{$xa->id_kelas}/anggota")->json('data.anggota.0.id_riwayat_kelas');
        $this->postJson("/api/master/anggota-kelas/{$idAnggota}/pindah", ['id_kelas' => $xia->id_kelas, 'alasan' => 'x'])->assertStatus(422);
        $this->postJson("/api/master/anggota-kelas/{$idAnggota}/pindah", ['id_kelas' => $xb->id_kelas, 'alasan' => 'Permintaan orang tua'])->assertOk();

        $this->assertDatabaseHas('riwayat_kelas', ['id_riwayat_kelas' => $idAnggota, 'id_kelas' => $xb->id_kelas]);
        $this->assertSame(1, MutasiKelas::where('id_riwayat_kelas', $idAnggota)->count());
    }

    public function test_relasi_orang_tua_anak(): void
    {
        $s = $this->siswa();
        $this->sebagai($this->operator);
        $idOrtu = $this->postJson('/api/master/orang-tua', ['nama' => 'Bapak'])->assertCreated()->json('data.id_orang_tua');

        $this->postJson("/api/master/siswa/{$s->id_siswa}/orang-tua", ['id_orang_tua' => $idOrtu, 'hubungan' => 'Ayah'])->assertOk();
        $this->getJson("/api/master/siswa/{$s->id_siswa}")->assertJsonPath('data.orang_tua.0.hubungan', 'Ayah');
        $this->deleteJson("/api/master/siswa/{$s->id_siswa}/orang-tua/{$idOrtu}")->assertOk();
    }

    public function test_impor_siswa_pratinjau_lalu_simpan_dengan_laporan_baris_gagal(): void
    {
        $this->kelas('X-A');
        $this->siswa(null, ['nis' => '9000']);
        $csv = "nis,nisn,nama,jenis_kelamin,agama,tempat_lahir,tanggal_lahir,alamat,tahun_masuk,kelas\n"
            ."3001,0011112222,Eko,L,Islam,Malang,2010-01-02,,2026,X-A\n"
            ."9000,,Duplikat,L,Islam,,,,,\n"
            ."3002,,Fani,P,Bukan Agama,,,,,X-Z\n"
            ."3003,,Gita,P,,,,,,\n";
        $file = UploadedFile::fake()->createWithContent('siswa.csv', $csv);
        $this->sebagai($this->operator);

        $res = $this->post('/api/master/siswa-impor/pratinjau', ['file' => $file], ['Accept' => 'application/json'])
            ->assertOk()->assertJsonPath('data.valid', 2)->assertJsonCount(2, 'data.gagal');
        $this->assertSame([3, 4], array_column($res->json('data.gagal'), 'baris'));
        $this->assertDatabaseMissing('siswa', ['nis' => '3001']);

        $this->postJson('/api/master/siswa-impor/simpan', ['token' => $res->json('data.token')])
            ->assertOk()->assertJsonPath('data.dibuat', 2)->assertJsonPath('data.ditempatkan', 1);
        $this->assertDatabaseHas('siswa', ['nis' => '3001', 'nisn' => '0011112222']);
        $this->postJson('/api/master/siswa-impor/simpan', ['token' => $res->json('data.token')])->assertNotFound();
    }

    public function test_penetapan_wali_kelas_memberi_dan_mencabut_peran_otomatis(): void
    {
        $user = $this->buatUser(['GURU']);
        $guru = $this->guru($user);
        $kelas = $this->kelas('X-B');
        $this->sebagai($this->kurikulum);

        $id = $this->putJson("/api/kurikulum/kelas/{$kelas->id_kelas}/wali", ['id_guru' => $guru->id_guru])->assertOk()->json('data.id_wali_kelas');
        $this->assertTrue($user->fresh()->hasRole('WALI_KELAS'));

        $this->deleteJson("/api/kurikulum/wali-kelas/{$id}")->assertOk();
        $this->assertFalse($user->fresh()->hasRole('WALI_KELAS'));
        $this->assertDatabaseHas('log_aktivitas', ['aksi' => 'cabut_peran_wali', 'entitas_id' => $user->id_user]);
    }

    public function test_mapel_agama_hanya_untuk_siswa_seagama_dan_peserta_semester_sama(): void
    {
        $islam = Agama::where('nama', 'Islam')->value('id_agama');
        $kristen = Agama::where('nama', 'Kristen')->value('id_agama');
        $kelas = $this->kelas('X-A');
        $a = $this->anggota($this->siswa(null, ['id_agama' => $islam]), $kelas);
        $b = $this->anggota($this->siswa(null, ['id_agama' => $kristen]), $kelas);
        $mapel = Mapel::create(['kode' => 'PAI', 'nama' => 'Pendidikan Agama Islam', 'id_agama' => $islam]);
        $rombel = Rombel::create(['id_semester' => $this->semesterAktif()->id_semester, 'id_mapel' => $mapel->id_mapel, 'nama' => 'PAI X']);

        $this->sebagai($this->kurikulum)->postJson("/api/kurikulum/rombel/{$rombel->id_rombel}/peserta", ['id_kelas' => [$kelas->id_kelas]])
            ->assertOk()->assertJsonPath('data.ditambahkan', 1)
            ->assertJsonPath("data.gagal.{$b->id_riwayat_kelas}", 'Agama siswa tidak sesuai dengan mapel agama ini.');
        $this->assertDatabaseHas('siswa_rombel', ['id_rombel' => $rombel->id_rombel, 'id_riwayat_kelas' => $a->id_riwayat_kelas]);
    }

    public function test_hanya_satu_semester_aktif(): void
    {
        $this->sebagai($this->kurikulum);
        $this->postJson('/api/kurikulum/tahun-ajaran', ['nama' => '2027/2029'])->assertStatus(422);
        $this->postJson('/api/kurikulum/tahun-ajaran', ['nama' => '2027/2028'])->assertCreated()->assertJsonCount(2, 'data.semester');

        $baru = Semester::whereHas('tahunAjaran', fn ($q) => $q->where('nama', '2027/2028'))->where('nama', 'GANJIL')->first();
        $this->postJson("/api/kurikulum/semester/{$baru->id_semester}/aktifkan")->assertOk();

        $this->assertSame(1, Semester::where('aktif', true)->count());
        $this->assertSame(1, TahunAjaran::where('aktif', true)->count());
        $this->assertTrue($baru->fresh()->aktif);
    }
}
