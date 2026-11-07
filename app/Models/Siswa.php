<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Siswa extends Model
{
    protected $table = 'siswa';

    protected $primaryKey = 'id_siswa';

    public $timestamps = false;

    protected $fillable = [
        'id_user',
        'nis',
        'nisn',
        'agama',
        'jenis_kelamin',
        'tahun_masuk',
        'urutan_masuk',
        'alamat',
        'nama',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function presensi(): HasMany
    {
        return $this->hasMany(Presensi::class, 'id_siswa', 'id_siswa');
    }

    public function qrSiswa(): HasOne
    {
        return $this->hasOne(QrSiswa::class, 'id_siswa', 'id_siswa');
    }
}