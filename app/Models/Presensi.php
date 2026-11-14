<?php

namespace App\Models;

use App\Models\RiwayatPerubahanPresensi;
use App\Models\Izin;
use App\Models\Sakit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Presensi extends Model
{
    protected $table = 'presensi';

    protected $primaryKey = 'id_presensi';

    public $timestamps = false;

    protected $fillable = [
        'id_siswa',
        'metode',
        'status',
        'tanggal',
        'waktu_presensi',
        'file_bukti',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(
            Siswa::class,
            'id_siswa',
            'id_siswa'
        );
    }

    public function izin(): HasOne
    {
        return $this->hasOne(
            Izin::class,
            'id_presensi',
            'id_presensi'
        );
    }

    public function sakit(): HasOne
    {
        return $this->hasOne(
            Sakit::class,
            'id_presensi',
            'id_presensi'
        );
    }

    public function riwayatPerubahan(): HasMany
    {
        return $this->hasMany(
            RiwayatPerubahanPresensi::class,
            'id_presensi',
            'id_presensi'
        )->orderByDesc('waktu_perubahan');
    }
}