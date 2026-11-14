<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sakit extends Model
{
    protected $table = 'sakit';

    protected $primaryKey = 'id_sakit';

    public $timestamps = false;

    protected $fillable = [
        'id_presensi',
        'file_bukti',
    ];

    public function presensi(): BelongsTo
    {
        return $this->belongsTo(Presensi::class, 'id_presensi', 'id_presensi');
    }
}