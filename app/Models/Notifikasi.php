<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notifikasi extends Model
{
    protected $table = 'notifikasi';

    protected $primaryKey = 'id_notifikasi';

    protected $fillable = ['id_user', 'jenis', 'judul', 'pesan', 'data', 'dibaca_at'];

    protected function casts(): array
    {
        return ['data' => 'array', 'dibaca_at' => 'datetime'];
    }
}
