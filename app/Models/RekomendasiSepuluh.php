<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Rekomendasi mapel untuk siswa kelas X.
 * Tabel: rekomendasi_sepuluh.
 */
class RekomendasiSepuluh extends Model
{
    protected $table = 'rekomendasi_sepuluh';

    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_sesi_tes',
        'id_mapel',
        'peringkat',
        'skor',
        'alasan',
    ];

    protected $casts = [
        'skor' => 'float',
        'peringkat' => 'integer',
    ];

    public function sesiTes()
    {
        return $this->belongsTo(SesiTes::class, 'id_sesi_tes', 'id_sesi_tes');
    }

    public function mapel()
    {
        return $this->belongsTo(Mapel::class, 'id_mapel', 'id_mapel');
    }
}
