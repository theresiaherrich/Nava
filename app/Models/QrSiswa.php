<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QrSiswa extends Model
{
    protected $table = 'qr_siswa';

    protected $primaryKey = 'id_qr';

    public $timestamps = false;

    protected $fillable = [
        'id_siswa',
        'kode_qr',
    ];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'id_siswa', 'id_siswa');
    }
}