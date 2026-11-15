<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaketNilai extends Model
{
    protected $table = 'paket_nilai';

    protected $fillable = ['rombel_id', 'periode_id', 'kelas_id', 'status', 'dikirim_oleh', 'waktu_kirim', 'dikunci_oleh', 'waktu_kunci'];

    protected function casts(): array
    {
        return ['waktu_kirim' => 'datetime', 'waktu_kunci' => 'datetime'];
    }

    public function rombel(): BelongsTo
    {
        return $this->belongsTo(Rombel::class, 'rombel_id');
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(PeriodeNilai::class, 'periode_id');
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function verifikasi(): HasMany
    {
        return $this->hasMany(VerifikasiNilai::class, 'paket_id');
    }
}
