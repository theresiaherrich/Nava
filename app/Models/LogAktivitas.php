<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogAktivitas extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'log_aktivitas';

    protected $primaryKey = 'id_log';

    protected $fillable = ['id_user', 'aksi', 'nama_entitas', 'entitas_id', 'alasan', 'data_sebelum', 'data_sesudah', 'ip_address'];

    protected function casts(): array
    {
        return ['data_sebelum' => 'array', 'data_sesudah' => 'array', 'created_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}
