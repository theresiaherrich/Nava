<?php

namespace Database\Seeders;

use App\Models\Agama;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\OrangTua;
use App\Models\RiwayatKelas;
use App\Models\Role;
use App\Models\Sekolah;
use App\Models\Semester;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;
use App\Models\WaliKelas;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * Data contoh untuk development (kelas, guru, siswa, nilai). Hanya jalan di APP_ENV local/testing dan
 * membaca kata sandi dari SEED_DEFAULT_PASSWORD, atau NAVA_DEMO_PASSWORD bila kosong (tidak ada sandi di kode).
 * Akun per peran (superadmin, operator, kurikulum, ...) ada di DemoAccountSeeder.
 */
class DevSeeder extends Seeder
{
    private string $password;

    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->command?->warn('DevSeeder dilewati: hanya untuk APP_ENV local/testing.');

            return;
        }

        $this->password = (string) (config('siakad.seed_default_password') ?: config('siakad.demo_password'));
        if (strlen($this->password) < (int) config('siakad.password.min')) {
            throw new RuntimeException('Isi NAVA_DEMO_PASSWORD (atau SEED_DEFAULT_PASSWORD) di .env (min. '.config('siakad.password.min').' karakter) sebelum seeding.');
        }

        $islam = Agama::where('nama', 'Islam')->value('id_agama');
        $kristen = Agama::where('nama', 'Kristen')->value('id_agama');

        Sekolah::firstOrCreate(['id_sekolah' => 1], [
            'npsn' => '20533708', 'nama' => 'SMA Negeri 1 Kota Malang', 'kota' => 'Malang',
            'alamat' => 'Jl. Tugu Utara No. 1, Kota Malang', 'kepala_sekolah' => 'Kepala Sekolah Contoh',
        ]);

        $ta = TahunAjaran::firstOrCreate(['nama' => '2026/2027'], ['aktif' => true]);
        $ganjil = Semester::firstOrCreate(['id_tahun_ajaran' => $ta->id_tahun_ajaran, 'nama' => 'GANJIL'], ['aktif' => true]);
        Semester::firstOrCreate(['id_tahun_ajaran' => $ta->id_tahun_ajaran, 'nama' => 'GENAP'], ['aktif' => false]);

        $kelas = [];
        foreach (['X-A' => 'X', 'X-B' => 'X', 'XI-A' => 'XI'] as $nama => $tingkat) {
            $kelas[$nama] = Kelas::firstOrCreate(
                ['id_tahun_ajaran' => $ta->id_tahun_ajaran, 'nama' => $nama],
                ['tingkat' => $tingkat, 'fase' => Kelas::faseUntuk($tingkat)],
            );
        }

        // Akun superadmin/operator/kurikulum/kesiswaan dibuat oleh DemoAccountSeeder (satu akun per peran).

        $guru1 = $this->guru('198001012005011001', 'ASN', 'Budi Santoso', 'L', $islam, 'budi');
        $guru2 = $this->guru(null, 'GTT', 'Sari Wulandari', 'P', $kristen, 'sari');

        // Sari = wali kelas X-B; peran WALI_KELAS diberikan otomatis lewat penetapan ini.
        WaliKelas::firstOrCreate(
            ['id_kelas' => $kelas['X-B']->id_kelas, 'id_semester' => $ganjil->id_semester],
            ['id_guru' => $guru2->id_guru],
        );
        $this->beriPeran($guru2->user, 'WALI_KELAS');
        $waliXa = $this->guru(null, 'GTT', 'Wali Contoh X-A', 'P', $islam, 'wali_xa');
        WaliKelas::firstOrCreate(
            ['id_kelas' => $kelas['X-A']->id_kelas, 'id_semester' => $ganjil->id_semester],
            ['id_guru' => $waliXa->id_guru],
        );
        $this->beriPeran($waliXa->user, 'WALI_KELAS');

        $siswa = [
            ['1001', '0081234561', 'Andi Pratama', 'L', $islam, 'X-B', 'andi'],
            ['1002', '0081234562', 'Bunga Lestari', 'P', $kristen, 'X-B', 'bunga'],
            ['1003', '0081234563', 'Citra Ayu', 'P', $islam, 'X-A', 'citra'],
        ];
        foreach (range(4, 12) as $nomor) {
            $siswa[] = [(string) (1000 + $nomor), (string) (8800000000 + $nomor),
                sprintf('Siswa Fiktif %02d', $nomor), $nomor % 2 ? 'L' : 'P', $islam,
                $nomor === 12 ? 'X-A' : 'X-B', sprintf('siswa_a2_%02d', $nomor)];
        }
        $profilSiswa = [];
        foreach ($siswa as [$nis, $nisn, $nama, $jk, $agama, $namaKelas, $username]) {
            $s = Siswa::firstOrCreate(['nis' => $nis], [
                'nisn' => $nisn, 'nama' => $nama, 'jenis_kelamin' => $jk, 'id_agama' => $agama, 'tahun_masuk' => 2026,
            ]);
            $this->tautkan($s, $this->akun($username, $nama, ['SISWA'], $username === 'citra'));
            RiwayatKelas::firstOrCreate(
                ['id_siswa' => $s->id_siswa, 'id_semester' => $ganjil->id_semester],
                ['id_kelas' => $kelas[$namaKelas]->id_kelas],
            );
            $profilSiswa[$username] = $s;
        }

        $ortu = OrangTua::firstOrCreate(['nama' => 'Hendra Pratama'], ['jenis_kelamin' => 'L', 'no_telepon' => '081200000001']);
        $ortu->anak()->syncWithoutDetaching([$profilSiswa['andi']->id_siswa => ['hubungan' => 'Ayah']]);
        $this->tautkan($ortu, $this->akun('hendra', 'Hendra Pratama', ['ORANG_TUA']));

        $this->call(DevPenilaianSeeder::class);

        $this->command?->info('Daftar akun contoh tersedia di docs/integration/03_AKUN_DUMMY.md. Sandi awal ditentukan oleh SEED_DEFAULT_PASSWORD.');
    }

    private function akun(string $username, string $nama, array $roles, bool $ubahSandi = false): User
    {
        $user = User::firstOrCreate(['username' => $username], [
            'nama' => $nama, 'password' => $this->password, 'status_akun' => 'aktif', 'must_change_password' => $ubahSandi,
        ]);
        foreach ($roles as $r) {
            $this->beriPeran($user, $r);
        }

        return $user;
    }

    private function beriPeran(User $user, string $kode): void
    {
        $user->roles()->syncWithoutDetaching([Role::where('kode', $kode)->value('id_role') => ['created_at' => now()]]);
        $user->sinkronkanRoleA1();
    }

    private function guru(?string $nip, string $status, string $nama, string $jk, ?int $agama, string $username): Guru
    {
        $g = Guru::firstOrCreate(['nama' => $nama], [
            'nip' => $nip, 'status_kepegawaian' => $status, 'jenis_kelamin' => $jk, 'id_agama' => $agama,
        ]);
        $this->tautkan($g, $this->akun($username, $nama, ['GURU']));

        return $g->load('user');
    }

    private function tautkan($profil, User $user): void
    {
        if ($profil->id_user === null) {
            $profil->forceFill(['id_user' => $user->id_user])->save();
        }
    }
}
