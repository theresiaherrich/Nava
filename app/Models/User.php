<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

// SEMENTARA: menyesuaikan dengan tabel `user` (id_user, username).
class User extends Authenticatable
{
    protected $table = 'user';
    protected $primaryKey = 'id_user';
    public $timestamps = false;

    protected $fillable = ['username', 'password', 'must_change_password', 'role'];
    protected $hidden = ['password'];
}
