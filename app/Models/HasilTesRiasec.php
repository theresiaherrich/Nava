<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Skor per dimensi RIASEC untuk satu sesi tes.
 * Tabel: hasil_tes_riasec. PK: id_tes_riasec.
 * Satu sesi_tes → 6 baris (satu per dimensi R,I,A,S,E,C).
 */
class HasilTesRiasec extends Model
{
    protected $table = 'hasil_tes_riasec';

    protected $primaryKey = 'id_tes_riasec';

    public $timestamps = false;

    protected $fillable = [
        'id_sesi_tes',
        'id_dimensi',
        'skor_mentah',
        'skor_normal',
    ];

    protected $casts = [
        'skor_mentah' => 'integer',
        'skor_normal' => 'float',
    ];

    public function sesiTes()
    {
        return $this->belongsTo(SesiTes::class, 'id_sesi_tes', 'id_sesi_tes');
    }

    public function dimensi()
    {
        return $this->belongsTo(DimensiRiasec::class, 'id_dimensi', 'id_dimensi');
    }
}
