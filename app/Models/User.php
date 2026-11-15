<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'user';

    protected $primaryKey = 'id_user';

    protected $fillable = [
        'username', 'password', 'nama', 'email', 'status_akun', 'must_change_password',
    ];

    protected $hidden = ['password', 'remember_token'];

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
        return $this->belongsToMany(Role::class, 'role_user', 'id_user', 'id_role', 'id_user', 'id_role');
    }

    public function siswa(): HasOne
    {
        return $this->hasOne(Siswa::class, 'id_user', 'id_user');
    }

    public function guru(): HasOne
    {
        return $this->hasOne(Guru::class, 'id_user', 'id_user');
    }

    public function orangTua(): HasMany
    {
        return $this->hasMany(OrangTua::class, 'id_user', 'id_user');
    }

    /** @return array<int, string> kode peran, mis. ['GURU', 'WALI_KELAS'] */
    public function kodeRoles(): array
    {
        return $this->roles()->pluck('kode')->all();
    }

    public function hasRole(string $kode): bool
    {
        return in_array($kode, $this->kodeRoles(), true);
    }

    public function isAktif(): bool
    {
        return $this->status_akun === 'aktif';
    }
}
