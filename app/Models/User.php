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

    private bool $punyaRolePivotCache = false;

    protected static function booted(): void
    {
        // Kode A1 (pendaftaran orang tua, tambah siswa) membuat akun tanpa nama,
        // sedangkan kolom user.nama di skema A2 wajib diisi.
        static::creating(function (User $user) {
            if (blank($user->nama)) {
                $user->nama = $user->username;
            }
        });
    }

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
     * Peran tunggal versi modul A1 ($user->role).
     * - Akun A2 (punya baris role_user): diturunkan dari peran aktif / peran prioritas,
     *   jadi kode A1 mengikuti peran yang sedang dipakai (mis. WALI_KELAS → GURU).
     * - Akun A1 murni (tanpa role_user): nilai kolom user.role.
     */
    protected function role(): Attribute
    {
        return Attribute::get(function (?string $value) {
            if (! $this->punyaRolePivot()) {
                return $value;
            }

            $kode = Auth::id() === $this->id_user ? AcademicAccess::peranAktif() : null;
            $kode ??= $this->kodeRoles()[0] ?? null;

            return self::keRoleA1($kode);
        });
    }

    /** Kode peran A2 → nilai enum user.role A1 (null bila tidak ada padanannya). */
    public static function keRoleA1(?string $kode): ?string
    {
        $role = $kode === null ? null : (self::PERAN_KE_ROLE_A1[$kode] ?? $kode);

        return in_array($role, self::ROLE_A1, true) ? $role : null;
    }

    /** Nilai enum kolom user.role (migrasi kompatibilitas A1/A4). */
    public const ROLE_A1 = ['SISWA', 'GURU', 'ORANG_TUA', 'AKADEMIK', 'KURIKULUM', 'BUDAYA_POSITIF', 'KESISWAAN', 'ADMIN'];

    /**
     * @return list<string> mis. ['GURU', 'WALI_KELAS'], diurutkan sesuai config('siakad.roles').
     * Sumbernya role_user. Hanya akun A1 murni (tanpa baris role_user) yang memakai kolom user.role,
     * agar nilai hasil pemetaan (mis. SUPERADMIN → ADMIN) tidak menambah peran A2.
     */
    public function kodeRoles(): array
    {
        if ($this->kodeRolesCache === null) {
            $kode = $this->roles()->pluck('kode')->all();
            $this->punyaRolePivotCache = $kode !== [];

            $roleA1 = $this->attributes['role'] ?? null;
            if ($kode === [] && $roleA1 !== null) {
                $kode = [$roleA1];
            }

            $urutan = array_keys(config('siakad.roles'));
            $posisi = fn (string $k) => ($i = array_search($k, $urutan, true)) === false ? PHP_INT_MAX : $i;
            usort($kode, fn ($a, $b) => $posisi($a) <=> $posisi($b));
            $this->kodeRolesCache = $kode;
        }

        return $this->kodeRolesCache;
    }

    private function punyaRolePivot(): bool
    {
        $this->kodeRoles();

        return $this->punyaRolePivotCache;
    }

    /**
     * Isi kolom user.role dari peran A2 prioritas, agar query mentah modul A1
     * (mis. pendaftaran orang tua: WHERE user.role = 'SISWA') ikut mengenali akun A2.
     * Panggil setiap kali role_user berubah. Akun tanpa role_user tidak diubah.
     */
    public function sinkronkanRoleA1(): void
    {
        $this->lupakanCacheRoles();
        if (! $this->punyaRolePivot()) {
            return;
        }

        $this->forceFill(['role' => self::keRoleA1($this->kodeRoles()[0] ?? null)])->saveQuietly();
    }

    public function lupakanCacheRoles(): void
    {
        $this->kodeRolesCache = null;
        $this->punyaRolePivotCache = false;
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
