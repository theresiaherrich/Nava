<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerifikasiNilai extends Model
{
    // Hanya created_at; riwayat verifikasi tidak pernah diubah (append-only).
    const UPDATED_AT = null;

    protected $table = 'verifikasi_nilai';

    protected $fillable = ['paket_id', 'user_id', 'keputusan', 'catatan'];

    public function paket(): BelongsTo
    {
        return $this->belongsTo(PaketNilai::class, 'paket_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
