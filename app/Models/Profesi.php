<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Master profesi. Tabel: profesi. PK: id_profesi.
 */
class Profesi extends Model
{
    protected $table = 'profesi';

    protected $primaryKey = 'id_profesi';

    public $timestamps = false;

    protected $fillable = ['nama', 'deskripsi', 'aktif'];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    /**
     * Program studi yang memiliki profesi ini.
     * Pivot: prodi_profesi (sesuai DB siakad_sma1).
     */
    public function programStudi()
    {
        return $this->belongsToMany(ProgramStudi::class, 'prodi_profesi', 'id_profesi', 'id_prodi');
    }

    public function scopeAktif($query)
    {
        return $query->where('aktif', 1);
    }
}
