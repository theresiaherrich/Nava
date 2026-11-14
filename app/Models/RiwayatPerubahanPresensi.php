<?php

namespace App\Models;

use App\Models\Presensi;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatPerubahanPresensi extends Model
{
    protected $table = 'riwayat_perubahan_presensi';

    protected $primaryKey = 'id_riwayat';

    public $timestamps = false;

    protected $fillable = [
        'id_presensi',
        'id_user',
        'status_sebelumnya',
        'status_baru',
        'alasan_perubahan',
        'waktu_perubahan',
    ];

    protected function casts(): array
    {
        return [
            'waktu_perubahan' => 'datetime',
        ];
    }

    public function presensi(): BelongsTo
    {
        return $this->belongsTo(
            Presensi::class,
            'id_presensi',
            'id_presensi'
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'id_user',
            'id_user'
        );
    }
}