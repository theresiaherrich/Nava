<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Jawaban tiap soal RIASEC per sesi tes.
 * Tabel: jawaban_riasec. PK: id_jawaban_riasec.
 */
class JawabanRiasec extends Model
{
    protected $table = 'jawaban_riasec';

    protected $primaryKey = 'id_jawaban_riasec';

    public $timestamps = false;

    protected $fillable = [
        'id_sesi_tes',
        'id_soal_riasec',
        'nilai',
    ];

    protected $casts = [
        'nilai' => 'integer',
    ];

    public function sesiTes()
    {
        return $this->belongsTo(SesiTes::class, 'id_sesi_tes', 'id_sesi_tes');
    }

    public function soal()
    {
        return $this->belongsTo(SoalRiasec::class, 'id_soal_riasec', 'id_soal_riasec');
    }
}
