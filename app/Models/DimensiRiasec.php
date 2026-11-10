<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Dimensi RIASEC. Tabel: dimensi_riasec. PK: id_dimensi.
 * Hanya deskripsi yang bisa diedit (oleh Administrator).
 */
class DimensiRiasec extends Model
{
    protected $table = 'dimensi_riasec';

    protected $primaryKey = 'id_dimensi';

    public $timestamps = false;

    protected $fillable = ['deskripsi']; // kode & nama tidak boleh diubah

    public function soal()
    {
        return $this->hasMany(SoalRiasec::class, 'id_dimensi', 'id_dimensi');
    }
}
