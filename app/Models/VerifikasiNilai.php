<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Riwayat keputusan paket nilai (append-only). */
class VerifikasiNilai extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'verifikasi_nilai';

    protected $primaryKey = 'id_verifikasi_nilai';

    protected $fillable = ['id_paket_nilai', 'id_user', 'aksi', 'catatan'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}
