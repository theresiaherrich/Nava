<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Bank soal RIASEC. Tabel: soal_riasec. PK: id_soal_riasec.
 * Urutan tes mengikuti id_soal_riasec ASC.
 */
class SoalRiasec extends Model
{
    protected $table = 'soal_riasec';

    protected $primaryKey = 'id_soal_riasec';

    public $timestamps = false;

    protected $fillable = ['id_dimensi', 'teks', 'aktif'];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    public function dimensi()
    {
        return $this->belongsTo(DimensiRiasec::class, 'id_dimensi', 'id_dimensi');
    }

    /**
     * Scope: hanya soal yang aktif, diurut by id.
     */
    public function scopeAktif($query)
    {
        return $query->where('aktif', true)->orderBy('id_soal_riasec');
    }
}
