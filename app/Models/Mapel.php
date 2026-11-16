<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mapel extends Model
{
    protected $table = 'mapel';

    protected $primaryKey = 'id_mapel';

    protected $fillable = [
        'kode', 'nama', 'kelompok', 'urutan_rapor', 'id_agama', 'kktp_default',
        'deskripsi_kuat', 'deskripsi_penguatan', 'aktif',
    ];

    protected function casts(): array
    {
        return ['aktif' => 'boolean', 'kktp_default' => 'float', 'urutan_rapor' => 'integer'];
    }

    public function agama(): BelongsTo
    {
        return $this->belongsTo(Agama::class, 'id_agama', 'id_agama');
    }

    public function isAgama(): bool
    {
        return $this->id_agama !== null;
    }
}
