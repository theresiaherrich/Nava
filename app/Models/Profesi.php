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

    protected $fillable = ['nama', 'deskripsi'];

    public function programStudi()
    {
        return $this->belongsToMany(ProgramStudi::class, 'profesi_prodi', 'id_profesi', 'id_prodi');
    }
}
