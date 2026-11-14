<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Predikat extends Model
{
    protected $table = 'predikat';

    protected $primaryKey = 'id_predikat';

    public $timestamps = false;

    protected $fillable = ['predikat', 'nilai_min', 'nilai_max', 'keterangan', 'aktif'];

    protected function casts(): array
    {
        return ['aktif' => 'boolean'];
    }
}
