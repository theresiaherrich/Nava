<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ekskul extends Model
{
    protected $table = 'ekskul';

    protected $primaryKey = 'id_ekskul';

    public $timestamps = false;

    protected $fillable = ['nama'];

    public function peserta(): HasMany
    {
        return $this->hasMany(PesertaEkskul::class, 'id_ekskul', 'id_ekskul');
    }
}
