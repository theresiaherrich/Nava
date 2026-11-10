<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Model akun pengguna.
 * Tabel: user (bukan users — sesuai database nava)
 * Primary key: id_user
 * Login: username atau NIP (guru) atau NIS (siswa)
 */
class User extends Authenticatable
{
    protected $table = 'user';

    protected $primaryKey = 'id_user';

    public $timestamps = false;

    protected $fillable = ['username', 'password', 'must_change_password', 'role'];

    protected $hidden = ['password'];

    protected $casts = [
        'must_change_password' => 'boolean',
    ];

    // Relasi ke profil spesifik
    public function siswa()
    {
        return $this->hasOne(Siswa::class, 'id_user', 'id_user');
    }

    public function guru()
    {
        return $this->hasOne(Guru::class, 'id_user', 'id_user');
    }

    public function guruBk()
    {
        return $this->hasOneThrough(GuruBk::class, Guru::class, 'id_user', 'id_guru', 'id_user', 'id_guru');
    }
}
