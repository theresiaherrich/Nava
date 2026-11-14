<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrangTua extends Model
{
    protected $table = 'orang_tua';

    // Tabel menggunakan primary key gabungan.
    // Eloquent tidak mendukung composite primary key
    // untuk operasi update/delete standar.
    protected $primaryKey = 'id_siswa';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'id_siswa',
        'hubungan',
        'id_user',
        'alamat',
        'no_telepon',
        'jenis_kelamin',
        'nama',
        'status_verifikasi',
        'diverifikasi_oleh',
        'waktu_verifikasi',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'id_user',
            'id_user'
        );
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(
            Siswa::class,
            'id_siswa',
            'id_siswa'
        );
    }
}
