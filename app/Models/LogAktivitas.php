<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogAktivitas extends Model
{
    protected $table = 'log_aktivitas';

    protected $fillable = ['user_id', 'aksi', 'nama_entitas', 'entitas_id', 'alasan', 'data_sebelum', 'data_sesudah'];

    protected function casts(): array
    {
        return ['data_sebelum' => 'array', 'data_sesudah' => 'array'];
    }
}
