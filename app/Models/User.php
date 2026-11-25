<?php

namespace App\Models;

use App\Support\AcademicAccess;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;

    public const STATUS = ['belum_aktif', 'aktif', 'nonaktif'];

    /**
     * Peran A2 (roles.kode) → nilai kolom user.role versi modul A1.
     * Kode lain (GURU, SISWA, ADMIN, dst.) sama di kedua modul.
     */
    public const PERAN_KE_ROLE_A1 = [
        'SUPERADMIN' => 'ADMIN',
        'WALI_KELAS' => 'GURU',
    ];

    protected $table = 'user';

    protected $primaryKey = 'id_user';

    protected $fillable = [
        'username', 'password', 'nama', 'email', 'status_akun', 'must_change_password',
        // Peran tunggal versi modul A1 (Presensi/pendaftaran orang tua).
        'role',
    ];

    protected $hidden = ['password', 'remember_token'];

    /** Cache kode peran per instance agar middleware tidak query berulang. */
    private ?array $kodeRolesCache = null;

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'must_change_password' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user', 'id_user', 'id_role')->withPivot('created_at');
    }

    public function siswa(): HasOne
    {
        return $this->hasOne(Siswa::class, 'id_user', 'id_user');
    }

    public function guru(): HasOne
    {
        return $this->hasOne(Guru::class, 'id_user', 'id_user');
    }

    public function orangTua(): HasOne
    {
        return $this->hasOne(OrangTua::class, 'id_user', 'id_user');
    }

    public function notifikasi(): HasMany
    {
        return $this->hasMany(Notifikasi::class, 'id_user', 'id_user');
    }

    /** Baris pendaftaran mandiri orang tua (modul A1/A4), satu baris per anak. */
    public function dataOrangTua(): HasMany
    {
        return $this->hasMany(OrangTua::class, 'id_user', 'id_user');
    }

    /** Anak dari pendaftaran mandiri orang tua (modul A1/A4), lewat kolom orang_tua.id_siswa. */
    public function anak(): BelongsToMany
    {
        return $this->belongsToMany(Siswa::class, 'orang_tua', 'id_user', 'id_siswa', 'id_user', 'id_siswa')
            ->withPivot([
                'id_orang_tua', 'hubungan', 'nama', 'alamat', 'no_telepon', 'jenis_kelamin',
                'status_verifikasi', 'diverifikasi_oleh', 'waktu_verifikasi',
            ]);
    }

    /**
     * Peran tunggal versi modul A1 ($user->role). Bila kolom user.role kosong (akun dibuat
     * lewat modul A2), nilainya diturunkan dari peran aktif / peran prioritas di role_user.
     */
    protected function role(): Attribute
    {
        return Attribute::get(function (?string $value) {
            if ($value !== null) {
                return $value;
            }

            $kode = Auth::id() === $this->id_user ? AcademicAccess::peranAktif() : null;
            $kode ??= $this->kodeRoles()[0] ?? null;

            return $kode === null ? null : (self::PERAN_KE_ROLE_A1[$kode] ?? $kode);
        });
    }

    /**
     * @return list<string> mis. ['GURU', 'WALI_KELAS'], diurutkan sesuai config('siakad.roles').
     * Akun yang dibuat modul A1 (tanpa baris role_user) memakai kolom user.role.
     */
    public function kodeRoles(): array
    {
        if ($this->kodeRolesCache === null) {
            $kode = $this->roles()->pluck('kode')->all();

            $roleA1 = $this->attributes['role'] ?? null;
            if ($roleA1 !== null && ! in_array($roleA1, $kode, true)) {
                $kode[] = $roleA1;
            }

            $urutan = array_keys(config('siakad.roles'));
            $posisi = fn (string $k) => ($i = array_search($k, $urutan, true)) === false ? PHP_INT_MAX : $i;
            usort($kode, fn ($a, $b) => $posisi($a) <=> $posisi($b));
            $this->kodeRolesCache = $kode;
        }

        return $this->kodeRolesCache;
    }

    public function lupakanCacheRoles(): void
    {
        $this->kodeRolesCache = null;
        $this->unsetRelation('roles');
    }

    public function hasRole(string ...$kode): bool
    {
        return array_intersect($kode, $this->kodeRoles()) !== [];
    }

    public function isAktif(): bool
    {
        return $this->status_akun === 'aktif';
    }
}
