<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogAktivitas extends Model
{
    protected $table = 'log_aktivitas';

    protected $fillable = [
        'id_user', 'aksi', 'nama_entitas', 'entitas_id', 'alasan', 'data_sebelum', 'data_sesudah',
    ];
    protected $fillable = ['user_id', 'aksi', 'nama_entitas', 'entitas_id', 'alasan', 'data_sebelum', 'data_sesudah'];

    protected function casts(): array
    {
        return ['data_sebelum' => 'array', 'data_sesudah' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}
