<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Master kelas. Tabel: kelas. PK: id_kelas.
 */
class Kelas extends Model
{
    protected $table = 'kelas';

    protected $primaryKey = 'id_kelas';

    public $timestamps = false;

    protected $fillable = ['nama', 'tingkat', 'fase', 'id_tahun_ajaran'];

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class, 'id_tahun_ajaran', 'id_tahun_ajaran');
    }

    public function riwayatKelas()
    {
        return $this->hasMany(RiwayatKelas::class, 'id_kelas', 'id_kelas');
    }
}
