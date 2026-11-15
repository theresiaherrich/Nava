<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NilaiTp extends Model
{
    protected $table = 'nilai_tp';

    protected $fillable = ['tp_id', 'siswa_rombel_id', 'skor', 'diubah_oleh'];

    protected function casts(): array
    {
        return ['skor' => 'decimal:2'];
    }

    public function tp(): BelongsTo
    {
        return $this->belongsTo(Tp::class, 'tp_id');
    }

    public function siswaRombel(): BelongsTo
    {
        return $this->belongsTo(SiswaRombel::class, 'siswa_rombel_id');
    }
}
