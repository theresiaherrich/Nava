<?php

namespace App\Services;

use App\Models\Guru;
use App\Models\Role;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AccountService
{
    public function __construct(private AuditService $audit) {}

    /**
     * Membuat akun beserta perannya. Jika kata sandi tidak diisi, dibuat otomatis
     * dan dikembalikan sekali saja untuk diserahkan ke pengguna.
     *
     * @return array{user: User, password: ?string}
     */
    public function buat(
        array $data,
        array $kodeRoles,
        string $status = 'aktif',
        ?array $profilGuru = null,
        bool $wajibGantiSandi = true,
    ): array {
        $dibuatOtomatis = empty($data['password']);
        $plain = $data['password'] ?? Str::random(10);

        return DB::transaction(function () use ($data, $kodeRoles, $status, $profilGuru, $wajibGantiSandi, $plain, $dibuatOtomatis) {
            $user = User::create([
                'username' => $data['username'],
                'password' => $plain,
                'nama' => $data['nama'],
                'email' => $data['email'] ?? null,
                'status_akun' => $status,
                'must_change_password' => $wajibGantiSandi,
            ]);

            $this->pasangPeran($user, $kodeRoles);

            if ($profilGuru !== null && in_array('GURU', $kodeRoles, true)) {
                Guru::create([
                    'id_user' => $user->id_user,
                    'nama' => $user->nama,
                    'nip' => $profilGuru['nip'] ?? null,
                    'status_kepegawaian' => $profilGuru['status_kepegawaian'] ?? null,
                ]);
            }

            $this->audit->catat(
                'buat_akun', 'user', $user->id_user, null, null,
                ['username' => $user->username, 'peran' => $kodeRoles, 'status_akun' => $status],
                Auth::id() ?? $user->id_user,
            );

            return ['user' => $user, 'password' => $dibuatOtomatis ? $plain : null];
        });
    }

    /** Pendaftaran mandiri: akun dibuat pending dan menunggu persetujuan admin. */
    public function daftar(array $data): User
    {
        $profil = $data['peran'] === 'GURU' ? ['nip' => $data['nip'] ?? null] : null;

        return $this->buat($data, [$data['peran']], 'pending', $profil, false)['user'];
    }

    public function pasangPeran(User $user, array $kodeRoles): void
    {
        $kodeRoles = array_values(array_unique($kodeRoles));
        $idRole = Role::whereIn('kode', $kodeRoles)->pluck('id_role')->all();

        if (count($idRole) !== count($kodeRoles)) {
            throw ValidationException::withMessages(['roles' => 'Ada peran yang tidak dikenal.']);
        }

        $user->roles()->sync($idRole);
    }

    public function ubahData(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            $kolom = array_keys($data);
            $sebelum = $user->only($kolom);

            $user->update($data);

            $this->audit->catat('ubah_akun', 'user', $user->id_user, null, $sebelum, $user->only($kolom));

            return $user->refresh();
        });
    }

    public function ubahPeran(User $user, array $kodeRoles): void
    {
        DB::transaction(function () use ($user, $kodeRoles) {
            $sebelum = ['peran' => $user->kodeRoles()];

            $this->pasangPeran($user, $kodeRoles);

            $this->audit->catat('ubah_peran', 'user', $user->id_user, null, $sebelum, ['peran' => $user->kodeRoles()]);
        });
    }

    public function setujui(User $user): void
    {
        DB::transaction(function () use ($user) {
            $sebelum = ['status_akun' => $user->status_akun];

            $user->update(['status_akun' => 'aktif']);

            $this->audit->catat('setujui_akses', 'user', $user->id_user, null, $sebelum, ['status_akun' => 'aktif']);
        });
    }

    public function ubahStatus(User $user, string $status, ?string $alasan): void
    {
        if ((int) Auth::id() === (int) $user->id_user) {
            throw ValidationException::withMessages(['status' => 'Anda tidak dapat mengubah status akun sendiri.']);
        }

        DB::transaction(function () use ($user, $status, $alasan) {
            $sebelum = ['status_akun' => $user->status_akun];

            $user->update(['status_akun' => $status]);

            $aksi = $status === 'nonaktif' ? 'nonaktifkan_akun' : 'ubah_status_akun';
            $this->audit->catat($aksi, 'user', $user->id_user, $alasan, $sebelum, ['status_akun' => $status]);
        });
    }

    /** Menghubungkan profil siswa/guru ke akun. Satu profil hanya boleh terhubung ke satu akun. */
    public function hubungkanProfil(User $user, string $tipe, int $id): void
    {
        $peranWajib = $tipe === 'siswa' ? 'SISWA' : 'GURU';

        if (! $user->hasRole($peranWajib)) {
            throw ValidationException::withMessages(['user' => "Akun harus memiliki peran {$peranWajib} terlebih dahulu."]);
        }

        $profil = $tipe === 'siswa' ? Siswa::find($id) : Guru::find($id);

        if (! $profil) {
            throw ValidationException::withMessages(['id' => 'Profil tidak ditemukan.']);
        }

        if ($profil->id_user && (int) $profil->id_user !== (int) $user->id_user) {
            throw ValidationException::withMessages(['id' => 'Profil sudah terhubung ke akun lain.']);
        }

        DB::transaction(function () use ($user, $tipe, $profil) {
            $sebelum = ['id_user' => $profil->id_user];

            $profil->update(['id_user' => $user->id_user]);

            $this->audit->catat('hubungkan_profil', $tipe, $profil->getKey(), null, $sebelum, ['id_user' => $user->id_user]);
        });
    }
}
