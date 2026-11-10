<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Master tahun ajaran. Tabel: tahun_ajaran. PK: id_tahun_ajaran.
 */
class TahunAjaran extends Model
{
    protected $table = 'tahun_ajaran';

    protected $primaryKey = 'id_tahun_ajaran';

    // timestamps: ada created_at/updated_at
    public $timestamps = true;

    protected $fillable = ['nama', 'aktif'];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    public function semester()
    {
        return $this->hasMany(Semester::class, 'id_tahun_ajaran', 'id_tahun_ajaran');
    }

    public function kelas()
    {
        return $this->hasMany(Kelas::class, 'id_tahun_ajaran', 'id_tahun_ajaran');
    }

    public static function aktif()
    {
        return static::where('aktif', true)->first();
    }
}
