<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Semester. Tabel: semester. PK: id_semester.
 */
class Semester extends Model
{
    protected $table = 'semester';

    protected $primaryKey = 'id_semester';

    public $timestamps = true;

    protected $fillable = ['id_tahun_ajaran', 'nama', 'aktif'];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class, 'id_tahun_ajaran', 'id_tahun_ajaran');
    }

    public function riwayatKelas()
    {
        return $this->hasMany(RiwayatKelas::class, 'id_semester', 'id_semester');
    }

    public static function aktif()
    {
        return static::where('aktif', true)->first();
    }
}
