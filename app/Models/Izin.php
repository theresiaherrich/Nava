<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Izin extends Model
{
    protected $table = 'izin';

    protected $primaryKey = 'id_izin';

    public $timestamps = false;

    protected $fillable = [
        'id_presensi',
        'file_bukti',
        'alasan',
    ];

    public function presensi(): BelongsTo
    {
        return $this->belongsTo(Presensi::class, 'id_presensi', 'id_presensi');
    }
}