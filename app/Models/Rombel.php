<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rombel extends Model
{
    protected $table = 'rombel';

    protected $primaryKey = 'id_rombel';

    protected $fillable = [
        'id_semester', 'id_mapel', 'nama', 'tingkat', 'jam', 'kelompok_rapor', 'metode_nilai',
    ];

    public function mapel(): BelongsTo
    {
        return $this->belongsTo(Mapel::class, 'id_mapel', 'id_mapel');
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class, 'id_semester', 'id_semester');
    }

    public function guru(): BelongsToMany
    {
        return $this->belongsToMany(Guru::class, 'guru_rombel', 'id_rombel', 'id_guru');
    }

    public function paket(): HasMany
    {
        return $this->hasMany(PaketNilai::class, 'id_rombel', 'id_rombel');
    }
}
