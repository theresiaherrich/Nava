<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PeriodeNilai extends Model
{
    protected $table = 'periode_nilai';

    protected $primaryKey = 'id_periode';

    protected $fillable = [
        'id_semester', 'tingkat', 'jenis', 'tanggal_mulai', 'tanggal_selesai', 'status',
    ];

    protected function casts(): array
    {
        return ['tanggal_mulai' => 'date', 'tanggal_selesai' => 'date'];
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class, 'id_semester', 'id_semester');
    }

    public function paket(): HasMany
    {
        return $this->hasMany(PaketNilai::class, 'id_periode', 'id_periode');
    }
}
