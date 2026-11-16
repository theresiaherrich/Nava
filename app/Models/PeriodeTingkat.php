<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeriodeTingkat extends Model
{
    public $timestamps = false;

    public $incrementing = false;

    protected $table = 'periode_tingkat';

    protected $primaryKey = null;

    protected $fillable = ['id_periode', 'tingkat'];
}
