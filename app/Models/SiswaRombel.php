<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiswaRombel extends Model
{
    protected $table = 'siswa_rombel';

    protected $fillable = ['rombel_id', 'siswa_kelas_id'];

    public function rombel(): BelongsTo
    {
        return $this->belongsTo(Rombel::class, 'rombel_id');
    }

    public function siswaKelas(): BelongsTo
    {
        return $this->belongsTo(SiswaKelas::class, 'siswa_kelas_id');
    }
}
