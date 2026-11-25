<?php

namespace App\Services;

use App\Exceptions\AturanBisnisException;
use App\Models\Guru;
use App\Models\OrangTua;
use App\Models\Role;
use App\Models\Siswa;
use App\Models\User;
use App\Models\WaliKelas;
use App\Support\AcademicAccess;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Pengelolaan akun & peran dengan hierarki SUPERADMIN → ADMIN → peran lain.
 * Batas peran yang dapat dikelola diatur di config('siakad.role_manageable').
 */
class AccountService
{
    /** Peran yang wajib punya profil tertaut, beserta model profilnya. */
    public const PROFIL = [
        'GURU' => ['kolom' => 'id_guru', 'model' => Guru::class],
        'SISWA' => ['kolom' => 'id_siswa', 'model' => Siswa::class],
        'ORANG_TUA' => ['kolom' => 'id_orang_tua', 'model' => OrangTua::class],
    ];

    public function __construct(private AuditService $audit) {}

    // ------------------------------------------------------------------ aturan hierarki

    /** @return list<string> */
    public function peranDapatDikelola(?string $peranAktif = null): array
    {
        $peranAktif ??= AcademicAccess::peranAktif();

        return config("siakad.role_manageable.{$peranAktif}", []);
    }

    /**
     * Akun target boleh dikelola bila semua perannya (selain peran otomatis) ada di daftar
     * yang boleh dikelola peran aktif. Akun sendiri dikelola lewat /auth, bukan di sini.
     * $hanyaStatus: SUPERADMIN boleh mengaktif/nonaktifkan & reset sandi SUPERADMIN lain.
     */
    public function pastikanBolehKelola(User $target, bool $hanyaStatus = false): void
    {
        if ((int) $target->id_user === (int) Auth::id()) {
            throw AturanBisnisException::dilarang('Akun sendiri tidak dapat diubah dari menu kelola akun.', 'SELF_MANAGEMENT');
        }

        $peranAktif = AcademicAccess::peranAktif();
        $boleh = $this->peranDapatDikelola($peranAktif);
        $peranTarget = array_values(array_diff($target->kodeRoles(), config('siakad.role_otomatis')));

        if ($hanyaStatus && $peranAktif === 'SUPERADMIN' && in_array('SUPERADMIN', $peranTarget, true)) {
            return;
        }

        if ($boleh === [] || array_diff($peranTarget, $boleh) !== []) {
            throw AturanBisnisException::dilarang('Peran aktif Anda tidak berwenang mengelola akun ini.', 'ROLE_HIERARCHY');
        }
    }

    /** @param list<string> $roles */
    public function pastikanPeranBoleh(array $roles): void
    {
        $roles = array_values(array_unique($roles));
        $otomatis = config('siakad.role_otomatis');
        $cli = config('siakad.role_khusus_cli');

        if ($roles === []) {
            throw new AturanBisnisException('Akun wajib memiliki minimal satu peran.', 'ROLE_REQUIRED', 422, ['roles' => ['Pilih minimal satu peran.']]);
        }
        if ($x = array_intersect($roles, $otomatis)) {
            throw new AturanBisnisException('Peran '.implode(', ', $x).' diberikan otomatis lewat penetapan wali kelas.', 'ROLE_AUTOMATIC', 422, ['roles' => ['Peran ini tidak dapat dipilih manual.']]);
        }
        if ($x = array_intersect($roles, $cli)) {
            throw AturanBisnisException::dilarang('Peran '.implode(', ', $x).' hanya dapat dibuat lewat perintah artisan.', 'ROLE_CLI_ONLY');
        }
        if ($x = array_diff($roles, $this->peranDapatDikelola())) {
            throw AturanBisnisException::dilarang('Anda tidak berwenang memberikan peran: '.implode(', ', $x).'.', 'ROLE_HIERARCHY');
        }
        if (in_array('SISWA', $roles, true) && count($roles) > 1) {
            throw new AturanBisnisException('Peran Siswa tidak dapat digabung dengan peran lain.', 'ROLE_CONFLICT', 422, ['roles' => ['Siswa harus berdiri sendiri.']]);
        }
    }

    // ------------------------------------------------------------------ operasi akun

    /**
     * @param  array{username:string,nama:string,email?:?string,password?:?string,status_akun?:string,must_change_password?:bool}  $data
     * @param  list<string>  $roles
     * @param  array<string,int>  $profil  mis. ['id_guru' => 3]
     * @return array{user: User, password: ?string} password hanya terisi bila dibuat otomatis
     */
    public function buat(array $data, array $roles, array $profil = []): array
    {
        $this->pastikanPeranBoleh($roles);

        $otomatis = blank($data['password'] ?? null);
        $plain = $otomatis ? self::passwordAcak() : $data['password'];

        $user = DB::transaction(function () use ($data, $roles, $profil, $plain) {
            $user = User::create([
                'username' => Str::lower($data['username']),
                'nama' => $data['nama'],
                'email' => $data['email'] ?? null,
                'password' => $plain,
                'status_akun' => $data['status_akun'] ?? 'aktif',
                'must_change_password' => $data['must_change_password'] ?? true,
            ]);

            $this->pasangPeran($user, $roles);
            $this->tautkanProfil($user, $roles, $profil);

            $this->audit->catat('buat_akun', 'user', $user->id_user, null, [
                'username' => $user->username, 'peran' => $roles, 'status_akun' => $user->status_akun, 'profil' => $profil,
            ]);

            return $user;
        });

        return ['user' => $user->fresh(), 'password' => $otomatis ? $plain : null];
    }

    public function ubah(User $user, array $data): User
    {
        $this->pastikanBolehKelola($user);

        return DB::transaction(function () use ($user, $data) {
            if (isset($data['username'])) {
                $data['username'] = Str::lower($data['username']);
            }
            $sebelum = $user->only(array_keys($data));
            $user->update($data);
            $this->audit->catat('ubah_akun', 'user', $user->id_user, $sebelum, $user->only(array_keys($data)));

            return $user->fresh();
        });
    }

    /** Ganti seluruh peran manual. Peran otomatis (WALI_KELAS) dipertahankan apa adanya. */
    public function ubahPeran(User $user, array $roles, array $profil = []): User
    {
        $this->pastikanBolehKelola($user);
        $this->pastikanPeranBoleh($roles);

        return DB::transaction(function () use ($user, $roles, $profil) {
            $sebelum = $user->kodeRoles();
            $tetap = array_intersect($sebelum, config('siakad.role_otomatis'));

            $this->pasangPeran($user, array_values(array_unique([...$roles, ...$tetap])));
            $this->tautkanProfil($user, $roles, $profil);

            $this->audit->catat('ubah_peran', 'user', $user->id_user, ['peran' => $sebelum], ['peran' => $user->kodeRoles()]);
            $this->hapusSesi($user); // peran aktif di sesi lama mungkin sudah tidak berlaku

            return $user->fresh();
        });
    }

    public function ubahStatus(User $user, string $status, ?string $alasan = null): User
    {
        $this->pastikanBolehKelola($user, hanyaStatus: true);

        return DB::transaction(function () use ($user, $status, $alasan) {
            if ($status !== 'aktif' && $user->hasRole('SUPERADMIN')) {
                $aktifLain = User::whereKeyNot($user->id_user)->where('status_akun', 'aktif')
                    ->whereHas('roles', fn ($q) => $q->where('kode', 'SUPERADMIN'))->lockForUpdate()->count();
                if ($aktifLain === 0) {
                    throw AturanBisnisException::dilarang('Super Admin terakhir tidak dapat dinonaktifkan.', 'LAST_SUPERADMIN');
                }
            }

            $sebelum = ['status_akun' => $user->status_akun];
            $user->update(['status_akun' => $status]);
            $this->audit->catat('ubah_status_akun', 'user', $user->id_user, $sebelum, ['status_akun' => $status], $alasan);

            if ($status !== 'aktif') {
                $this->hapusSesi($user);
            }

            return $user->fresh();
        });
    }

    /** @return string kata sandi sementara, ditampilkan sekali. */
    public function resetPassword(User $user): string
    {
        $this->pastikanBolehKelola($user, hanyaStatus: true);
        $plain = self::passwordAcak();

        DB::transaction(function () use ($user, $plain) {
            $user->forceFill(['password' => $plain, 'must_change_password' => true, 'remember_token' => null])->save();
            $this->audit->catat('reset_password', 'user', $user->id_user, null, ['must_change_password' => true]);
            $this->hapusSesi($user);
        });

        return $plain;
    }

    /**
     * Buat akun massal dari profil yang belum punya akun.
     *
     * @param  'siswa'|'guru'|'orang_tua'  $jenis
     * @param  list<int>|null  $ids  null = semua profil tanpa akun
     * @return list<array{username:string,password:string,nama:string,profil_id:int}>
     */
    public function buatMassal(string $jenis, ?array $ids = null): array
    {
        $peran = ['siswa' => 'SISWA', 'guru' => 'GURU', 'orang_tua' => 'ORANG_TUA'][$jenis];
        $this->pastikanPeranBoleh([$peran]);

        $query = match ($jenis) {
            'siswa' => Siswa::query()->where('status', 'aktif'),
            'guru' => Guru::query()->where('aktif', true),
            'orang_tua' => OrangTua::query()->with('anak'),
        };
        $profil = $query->whereNull('id_user')
            ->when($ids !== null, fn ($q) => $q->whereKey($ids))
            ->orderBy($query->getModel()->getKeyName())
            ->get();

        return DB::transaction(function () use ($profil, $jenis, $peran) {
            $hasil = [];
            $idRole = Role::where('kode', $peran)->value('id_role');

            foreach ($profil as $p) {
                $plain = self::passwordAcak();
                $user = User::create([
                    'username' => $this->usernameUnik($this->usernameDasar($jenis, $p)),
                    'nama' => $p->nama,
                    'password' => $plain,
                    'status_akun' => 'aktif',
                    'must_change_password' => true,
                ]);
                $user->roles()->attach($idRole, ['created_at' => now()]);
                $user->sinkronkanRoleA1();
                $p->forceFill(['id_user' => $user->id_user])->save();

                $hasil[] = ['username' => $user->username, 'password' => $plain, 'nama' => $p->nama, 'profil_id' => (int) $p->getKey()];
            }

            $this->audit->catat('buat_akun_massal', $jenis, null, null, [
                'peran' => $peran, 'jumlah' => count($hasil), 'username' => array_column($hasil, 'username'),
            ]);

            return $hasil;
        });
    }

    /**
     * WALI_KELAS mengikuti data wali_kelas: diberikan bila guru menjadi wali di semester mana pun
     * yang masih aktif/akan datang, dicabut bila tidak ada lagi penetapan.
     */
    public function sinkronPeranWali(Guru $guru): void
    {
        $user = $guru->user;
        if (! $user) {
            return;
        }

        $masihWali = WaliKelas::where('id_guru', $guru->id_guru)->exists();
        $punya = $user->hasRole('WALI_KELAS');

        if ($masihWali === $punya) {
            return;
        }

        $idRole = Role::where('kode', 'WALI_KELAS')->value('id_role');
        $masihWali
            ? $user->roles()->syncWithoutDetaching([$idRole => ['created_at' => now()]])
            : $user->roles()->detach($idRole);
        $user->sinkronkanRoleA1();

        $this->audit->catat($masihWali ? 'beri_peran_wali' : 'cabut_peran_wali', 'user', $user->id_user,
            ['peran' => $punya ? ['WALI_KELAS'] : []], ['peran' => $masihWali ? ['WALI_KELAS'] : []]);
    }

    // ------------------------------------------------------------------ util

    /** Sandi acak yang memenuhi kebijakan (huruf besar, kecil, angka, simbol). */
    public static function passwordAcak(): string
    {
        $panjang = max(12, (int) config('siakad.password.panjang_acak', 12));
        $set = ['ABCDEFGHJKLMNPQRSTUVWXYZ', 'abcdefghijkmnopqrstuvwxyz', '23456789', '!@#$%*?'];
        $chars = array_map(fn ($s) => $s[random_int(0, strlen($s) - 1)], $set);
        $semua = implode('', $set);
        while (count($chars) < $panjang) {
            $chars[] = $semua[random_int(0, strlen($semua) - 1)];
        }
        shuffle($chars);

        return implode('', $chars);
    }

    private function pasangPeran(User $user, array $roles): void
    {
        $id = Role::whereIn('kode', $roles)->pluck('id_role');
        if ($id->count() !== count(array_unique($roles))) {
            throw new AturanBisnisException('Ada peran yang tidak dikenal.', 'ROLE_UNKNOWN', 422, ['roles' => ['Peran tidak dikenal.']]);
        }

        $user->roles()->sync($id->mapWithKeys(fn ($i) => [$i => ['created_at' => now()]])->all());
        $user->sinkronkanRoleA1();
    }

    /** Tautkan profil untuk peran yang mensyaratkannya. Satu profil hanya untuk satu akun. */
    private function tautkanProfil(User $user, array $roles, array $profil): void
    {
        foreach (self::PROFIL as $peran => $cfg) {
            if (! in_array($peran, $roles, true)) {
                continue;
            }

            /** @var class-string<Model> $model */
            $model = $cfg['model'];
            $sudah = $model::where('id_user', $user->id_user)->first();
            $id = $profil[$cfg['kolom']] ?? null;

            if ($sudah && ($id === null || (int) $id === (int) $sudah->getKey())) {
                continue;
            }
            if ($id === null) {
                throw new AturanBisnisException("Peran {$peran} wajib ditautkan ke data profilnya.", 'PROFILE_REQUIRED', 422, [$cfg['kolom'] => ['Wajib diisi untuk peran ini.']]);
            }

            $p = $model::whereKey($id)->lockForUpdate()->first()
                ?? throw new AturanBisnisException('Profil tidak ditemukan.', 'PROFILE_NOT_FOUND', 422, [$cfg['kolom'] => ['Profil tidak ditemukan.']]);
            if ($p->id_user !== null && (int) $p->id_user !== (int) $user->id_user) {
                throw new AturanBisnisException('Profil sudah tertaut ke akun lain.', 'PROFILE_ALREADY_LINKED', 409, [$cfg['kolom'] => ['Profil sudah memiliki akun.']]);
            }

            $sudah?->forceFill(['id_user' => null])->save();
            $p->forceFill(['id_user' => $user->id_user])->save();
        }
    }

    private function usernameDasar(string $jenis, Model $p): string
    {
        return Str::lower(match ($jenis) {
            'siswa' => $p->nisn ?: $p->nis,
            'guru' => $p->nip ?: 'gtt'.str_pad((string) $p->id_guru, 4, '0', STR_PAD_LEFT),
            'orang_tua' => $this->usernameOrangTua($p),
        });
    }

    private function usernameOrangTua(OrangTua $p): string
    {
        /** @var Collection<int, Siswa> $anak */
        $anak = $p->anak->sortBy('id_siswa');
        $pertama = $anak->first();
        if (! $pertama) {
            return 'ortu'.str_pad((string) $p->id_orang_tua, 4, '0', STR_PAD_LEFT);
        }
        $kode = ['Ayah' => 'a', 'Ibu' => 'i', 'Wali' => 'w'][$pertama->pivot->hubungan] ?? 'w';

        return 'ortu'.($pertama->nisn ?: $pertama->nis).$kode;
    }

    private function usernameUnik(string $dasar): string
    {
        $kandidat = $dasar;
        $i = 2;
        while (User::where('username', $kandidat)->exists()) {
            $kandidat = $dasar.'-'.$i++;
        }

        return $kandidat;
    }

    private function hapusSesi(User $user): void
    {
        if (config('session.driver') === 'database') {
            DB::table(config('session.table', 'sessions'))->where('user_id', $user->id_user)->delete();
        }
    }
}
