<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Siswa extends Model
{
    public const STATUS = ['aktif', 'lulus', 'pindah', 'keluar'];

    protected $table = 'siswa';

    protected $primaryKey = 'id_siswa';

    protected $fillable = [
        'nis', 'nisn', 'nama', 'jenis_kelamin', 'id_agama', 'tempat_lahir', 'tanggal_lahir',
        'alamat', 'tahun_masuk', 'status',
    ];

    protected function casts(): array
    {
        return ['tanggal_lahir' => 'date:Y-m-d'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function agama(): BelongsTo
    {
        return $this->belongsTo(Agama::class, 'id_agama', 'id_agama');
    }

    public function riwayatKelas(): HasMany
    {
        return $this->hasMany(RiwayatKelas::class, 'id_siswa', 'id_siswa');
    }

    public function orangTua(): BelongsToMany
    {
        return $this->belongsToMany(OrangTua::class, 'orang_tua_siswa', 'id_siswa', 'id_orang_tua')
            ->withPivot('hubungan')->withTimestamps();
    }
}
