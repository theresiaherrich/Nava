<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LokasiSekolah extends Model
{
    protected $table = 'lokasi_sekolah';

    protected $primaryKey = 'id_lokasi';

    public $timestamps = false;

    protected $fillable = [
        'latitude',
        'longitude',
        'nama_lokasi',
        'radius',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'radius' => 'integer',
        ];
    }
}