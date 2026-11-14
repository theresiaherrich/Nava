<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kelas extends Model
{
    protected $table = 'kelas';

    protected $primaryKey = 'id_kelas';

    public $timestamps = false;

    protected $fillable = [
        'nama',
        'tingkat',
        'fase',
        'id_tahun_ajaran',
    ];

    public function riwayatKelas(): HasMany
    {
        return $this->hasMany(
            RiwayatKelas::class,
            'id_kelas',
            'id_kelas'
        );
    }

    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(
            TahunAjaran::class,
            'id_tahun_ajaran',
            'id_tahun_ajaran'
        );
    }
}