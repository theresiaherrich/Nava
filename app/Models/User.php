<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;

    public const STATUS = ['belum_aktif', 'aktif', 'nonaktif'];

    protected $table = 'user';

    protected $primaryKey = 'id_user';

    protected $fillable = ['username', 'password', 'nama', 'email', 'status_akun', 'must_change_password'];

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

    /** @return list<string> mis. ['GURU', 'WALI_KELAS'], diurutkan sesuai config('siakad.roles'). */
    public function kodeRoles(): array
    {
        if ($this->kodeRolesCache === null) {
            $kode = $this->roles()->pluck('kode')->all();
            $urutan = array_keys(config('siakad.roles'));
            usort($kode, fn ($a, $b) => array_search($a, $urutan, true) <=> array_search($b, $urutan, true));
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
