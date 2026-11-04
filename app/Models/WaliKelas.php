<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaliKelas extends Model
{
    protected $table = 'wali_kelas';

    protected $fillable = ['kelas_id', 'guru_id'];

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class);
    }
}
