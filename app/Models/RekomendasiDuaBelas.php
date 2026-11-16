<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Rekomendasi prodi untuk siswa kelas XII.
 * Tabel: rekomendasi_dua_belas.
 */
class RekomendasiDuaBelas extends Model
{
    protected $table = 'rekomendasi_dua_belas';

    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_sesi_tes',
        'id_prodi',
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

    public function prodi()
    {
        return $this->belongsTo(ProgramStudi::class, 'id_prodi', 'id_prodi');
    }
}
