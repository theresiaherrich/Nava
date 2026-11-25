<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class OrangTua extends Model
{
    protected $table = 'orang_tua';

    protected $primaryKey = 'id_orang_tua';

    protected $fillable = [
        'nama', 'jenis_kelamin', 'no_telepon', 'alamat',
        // Pendaftaran mandiri orang tua (modul A1/A4).
        'id_siswa', 'hubungan', 'id_user', 'status_verifikasi', 'diverifikasi_oleh', 'waktu_verifikasi',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    /** Anak dari pendaftaran mandiri (modul A1/A4), kolom orang_tua.id_siswa. */
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'id_siswa', 'id_siswa');
    }

    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh', 'id_user');
    }

    public function anak(): BelongsToMany
    {
        return $this->belongsToMany(Siswa::class, 'orang_tua_siswa', 'id_orang_tua', 'id_siswa')
            ->withPivot('hubungan')->withTimestamps();
    }
}
