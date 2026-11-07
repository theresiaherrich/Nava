<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Model
{
    protected $table = 'user';

    protected $primaryKey = 'id_user';

    public $timestamps = false;

    protected $fillable = [
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
}