<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agama extends Model
{
    public $timestamps = false;

    protected $table = 'agama';

    protected $primaryKey = 'id_agama';

    protected $fillable = ['nama'];
}
