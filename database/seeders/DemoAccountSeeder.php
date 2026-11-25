<?php

namespace Database\Seeders;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\OrangTua;
use App\Models\RiwayatKelas;
use App\Models\Role;
use App\Models\Rombel;
use App\Models\Semester;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;
use App\Services\WaliKelasService;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * Satu akun demo untuk setiap peran, khusus APP_ENV=local, agar semua halaman bisa dicoba
 * langsung dari halaman login. Sandi dibaca dari NAVA_DEMO_PASSWORD (tidak ada sandi di kode).
 *
 * Aman dijalankan berulang (updateOrCreate): sandi, status, dan peran dikembalikan ke kondisi demo.
 *   php artisan db:seed --class=DemoAccountSeeder
 */
class DemoAccountSeeder extends Seeder
{
    /** username => [nama, peran]. WALI_KELAS tidak ditulis di sini: diberikan otomatis lewat penetapan wali kelas. */
    public const AKUN = [
        'superadmin' => ['Super Admin Demo', ['SUPERADMIN']],
        'operator' => ['Operator Demo', ['ADMIN']],
        'kurikulum' => ['Admin Kurikulum Demo', ['KURIKULUM']],
        'kesiswaan' => ['Admin Kesiswaan Demo', ['KESISWAAN']],
        'guru_mapel' => ['Guru Mapel Demo', ['GURU']],
        'wali_kelas' => ['Wali Kelas Demo', ['GURU']],
        'guru_bk' => ['Guru BK Demo', ['BUDAYA_POSITIF']],
        'siswa_demo' => ['Siswa Demo', ['SISWA']],
        'ortu_demo' => ['Orang Tua Demo', ['ORANG_TUA']],
    ];

    private const KELAS_WALI = 'X-B';

    private string $password;

    public function __construct(private WaliKelasService $waliKelas) {}

    public function run(): void
    {
        if (! app()->environment('local')) {
            $this->command?->warn('DemoAccountSeeder dilewati: hanya untuk APP_ENV=local.');

            return;
        }

        $this->password = (string) config('siakad.demo_password');
        $min = (int) config('siakad.demo_password_min');
        if (mb_strlen($this->password) < $min) {
            throw new RuntimeException("Isi NAVA_DEMO_PASSWORD di .env (min. {$min} karakter) sebelum membuat akun demo.");
        }

        $akun = [];
        foreach (self::AKUN as $username => [$nama, $peran]) {
            $akun[$username] = $this->akun($username, $nama, $peran);
        }

        [$semester, $kelasXB] = $this->kelasWali();

        // Guru mapel: profil guru + ikut mengampu Matematika X bila rombel contoh (DevSeeder) ada.
        $guruMapel = $this->profilGuru($akun['guru_mapel'], 'L');
        $mtk = Rombel::where('nama', 'Matematika X')->where('id_semester', $semester->id_semester)->first();
        $mtk?->guru()->syncWithoutDetaching([$guruMapel->id_guru]);

        // Wali kelas X-B lewat layanan A2 (peran WALI_KELAS diberikan otomatis, wali lama dicabut).
        $guruWali = $this->profilGuru($akun['wali_kelas'], 'P');
        $this->waliKelas->tetapkan($kelasXB, $guruWali, $semester);

        // Siswa demo = anggota X-B semester aktif.
        $siswa = Siswa::where('id_user', $akun['siswa_demo']->id_user)->first()
            ?? Siswa::firstOrNew(['nis' => 'DEMO-001']);
        $siswa->fill(['nis' => 'DEMO-001', 'nama' => 'Siswa Demo', 'jenis_kelamin' => 'L', 'tahun_masuk' => 2026, 'status' => 'aktif'])
            ->forceFill(['id_user' => $akun['siswa_demo']->id_user])->save();
        RiwayatKelas::updateOrCreate(
            ['id_siswa' => $siswa->id_siswa, 'id_semester' => $semester->id_semester],
            ['id_kelas' => $kelasXB->id_kelas],
        );

        // Orang tua demo = ibu dari siswa demo.
        $ortu = OrangTua::firstOrNew(['id_user' => $akun['ortu_demo']->id_user]);
        $ortu->fill(['nama' => 'Orang Tua Demo', 'jenis_kelamin' => 'P'])->forceFill(['id_user' => $akun['ortu_demo']->id_user])->save();
        $ortu->anak()->syncWithoutDetaching([$siswa->id_siswa => ['hubungan' => 'Ibu']]);

        $this->command?->info('Akun demo siap ('.implode(', ', array_keys(self::AKUN)).'). Sandi = NAVA_DEMO_PASSWORD. Lihat docs/TESTING_LOKAL.md.');
    }

    /** @param list<string> $peran */
    private function akun(string $username, string $nama, array $peran): User
    {
        $user = User::updateOrCreate(['username' => $username], [
            'nama' => $nama,
            'password' => $this->password,
            'status_akun' => 'aktif',
            'must_change_password' => false,
        ]);

        $user->roles()->sync(Role::whereIn('kode', $peran)->pluck('id_role')->mapWithKeys(fn ($id) => [$id => ['created_at' => now()]])->all());
        $user->sinkronkanRoleA1();

        return $user;
    }

    private function profilGuru(User $user, string $jenisKelamin): Guru
    {
        $guru = Guru::firstOrNew(['id_user' => $user->id_user]);
        $guru->fill(['nama' => $user->nama, 'status_kepegawaian' => 'GTT', 'jenis_kelamin' => $jenisKelamin, 'aktif' => true])
            ->forceFill(['id_user' => $user->id_user])->save();

        return $guru->load('user');
    }

    /** Semester aktif dan kelas X-B; dibuat bila belum ada (mis. database tanpa data contoh). */
    private function kelasWali(): array
    {
        $semester = Semester::aktif();
        if (! $semester) {
            $ta = TahunAjaran::firstOrCreate(['nama' => '2026/2027'], ['aktif' => true]);
            $semester = Semester::firstOrCreate(['id_tahun_ajaran' => $ta->id_tahun_ajaran, 'nama' => 'GANJIL'], ['aktif' => true]);
            Semester::firstOrCreate(['id_tahun_ajaran' => $ta->id_tahun_ajaran, 'nama' => 'GENAP'], ['aktif' => false]);
        }

        $kelas = Kelas::firstOrCreate(
            ['id_tahun_ajaran' => $semester->id_tahun_ajaran, 'nama' => self::KELAS_WALI],
            ['tingkat' => 'X', 'fase' => Kelas::faseUntuk('X')],
        );

        return [$semester, $kelas];
    }
}
