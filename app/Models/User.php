<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'user';

    protected $primaryKey = 'id_user';

    public $timestamps = false;

    protected $fillable = [
        'nama',
        'username',
        'password',
        'must_change_password',
        'role',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'must_change_password' => 'boolean',
        ];
    }

    public function siswa(): HasOne
    {
        return $this->hasOne(Siswa::class, 'id_user', 'id_user');
    }

    public function guru(): HasOne
    {
        return $this->hasOne(Guru::class, 'id_user', 'id_user');
    }

    public function dataOrangTua(): HasMany
    {
        return $this->hasMany(
            OrangTua::class,
            'id_user',
            'id_user'
        );
    }

    public function anak(): BelongsToMany
    {
        return $this->belongsToMany(
            Siswa::class,
            'orang_tua',
            'id_user',
            'id_siswa',
            'id_user',
            'id_siswa'
        )->withPivot([
            'id_orang_tua',
            'hubungan',
            'nama',
            'alamat',
            'no_telepon',
            'jenis_kelamin',
            'status_verifikasi',
            'diverifikasi_oleh',
            'waktu_verifikasi',
        ]);
    }
}
