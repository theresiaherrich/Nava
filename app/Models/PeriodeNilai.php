<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeriodeNilai extends Model
{
    protected $table = 'periode_nilai';

    protected $fillable = ['semester_id', 'jenis', 'tingkat', 'tanggal_buka', 'tanggal_tutup', 'dikunci'];

    protected function casts(): array
    {
        return ['tingkat' => 'integer', 'tanggal_buka' => 'datetime', 'tanggal_tutup' => 'datetime', 'dikunci' => 'boolean'];
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function terbuka(): bool
    {
        return ! $this->dikunci && now()->between($this->tanggal_buka, $this->tanggal_tutup);
    }
}
