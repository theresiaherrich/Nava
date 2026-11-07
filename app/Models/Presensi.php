<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Presensi extends Model
{
    protected $table = 'presensi';

    protected $primaryKey = 'id_presensi';

    public $timestamps = false;

    protected $fillable = [
        'id_siswa',
        'metode',
        'hari',
        'tanggal',
        'waktu_presensi',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'id_siswa', 'id_siswa');
    }

    public function izin(): HasMany
    {
        return $this->hasMany(Izin::class, 'id_presensi', 'id_presensi');
    }

    public function sakit(): HasMany
    {
        return $this->hasMany(Sakit::class, 'id_presensi', 'id_presensi');
    }
}