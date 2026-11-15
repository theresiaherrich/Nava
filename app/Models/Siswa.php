<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Siswa extends Model
{
    protected $table = 'siswa';

    protected $primaryKey = 'id_siswa';

    protected $fillable = [
        'id_user', 'nis', 'nisn', 'nama', 'agama', 'jenis_kelamin',
        'tahun_masuk', 'urutan_masuk', 'alamat',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function riwayatKelas(): HasMany
    {
        return $this->hasMany(RiwayatKelas::class, 'id_siswa', 'id_siswa');
    }

    public function orangTua(): HasMany
    {
        return $this->hasMany(OrangTua::class, 'id_siswa', 'id_siswa');
    }
}
