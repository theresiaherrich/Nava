<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Master mata pelajaran. Tabel: mapel. PK: id_mapel.
 */
class Mapel extends Model
{
    protected $table = 'mapel';

    protected $primaryKey = 'id_mapel';

    public $timestamps = false;

    protected $fillable = ['id_dimensi', 'kode', 'nama'];

    public function dimensi()
    {
        return $this->belongsTo(DimensiRiasec::class, 'id_dimensi', 'id_dimensi');
    }

    public function profilMapel()
    {
        return $this->hasOne(ProfilMapel::class, 'id_mapel', 'id_mapel');
    }
}
