<?php

namespace Tests\Support;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\OrangTua;
use App\Models\RiwayatKelas;
use App\Models\Role;
use App\Models\Semester;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;
use App\Support\AcademicAccess;
use Database\Seeders\AgamaSeeder;
use Database\Seeders\RoleSeeder;

/** Pembuat data uji kecil yang dipakai bersama oleh tes fitur. */
trait SiakadFixtures
{
    protected function seedReferensi(): void
    {
        $this->seed([RoleSeeder::class, AgamaSeeder::class]);
    }

    /** @param list<string> $roles */
    protected function buatUser(array $roles, array $attr = []): User
    {
        $user = User::factory()->create($attr);
        $user->roles()->attach(Role::whereIn('kode', $roles)->pluck('id_role'), ['created_at' => now()]);

        return $user->fresh();
    }

    /** Login sebagai user dengan peran aktif tertentu. */
    protected function sebagai(User $user, ?string $peran = null): static
    {
        $this->actingAs($user);
        $this->withSession([AcademicAccess::SESSION_KEY => $peran ?? $user->kodeRoles()[0]]);

        return $this;
    }

    protected function semesterAktif(): Semester
    {
        $ta = TahunAjaran::firstOrCreate(['nama' => '2026/2027'], ['aktif' => true]);

        return Semester::firstOrCreate(['id_tahun_ajaran' => $ta->id_tahun_ajaran, 'nama' => 'GANJIL'], ['aktif' => true]);
    }

    protected function kelas(string $nama = 'X-A', string $tingkat = 'X'): Kelas
    {
        $sem = $this->semesterAktif();

        return Kelas::firstOrCreate(
            ['id_tahun_ajaran' => $sem->id_tahun_ajaran, 'nama' => $nama],
            ['tingkat' => $tingkat, 'fase' => Kelas::faseUntuk($tingkat)],
        );
    }

    protected function guru(?User $user = null, array $attr = []): Guru
    {
        $g = Guru::create($attr + ['nama' => 'Guru '.fake()->lastName(), 'status_kepegawaian' => 'GTT']);
        if ($user) {
            $g->forceFill(['id_user' => $user->id_user])->save();
        }

        return $g;
    }

    protected function siswa(?User $user = null, array $attr = []): Siswa
    {
        static $n = 0;
        $n++;
        $s = Siswa::create($attr + ['nis' => 'S'.str_pad((string) $n, 5, '0', STR_PAD_LEFT).fake()->unique()->numerify('###'), 'nama' => 'Siswa '.$n]);
        if ($user) {
            $s->forceFill(['id_user' => $user->id_user])->save();
        }

        return $s;
    }

    protected function anggota(Siswa $siswa, Kelas $kelas): RiwayatKelas
    {
        return RiwayatKelas::create([
            'id_siswa' => $siswa->id_siswa, 'id_kelas' => $kelas->id_kelas, 'id_semester' => $this->semesterAktif()->id_semester,
        ]);
    }

    protected function orangTua(User $user, Siswa ...$anak): OrangTua
    {
        $o = OrangTua::create(['nama' => $user->nama]);
        $o->forceFill(['id_user' => $user->id_user])->save();
        foreach ($anak as $s) {
            $o->anak()->attach($s->id_siswa, ['hubungan' => 'Wali']);
        }

        return $o;
    }
}
