<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Anggota kelas (ERD: anggota_kelas) untuk satu semester. */
class RiwayatKelas extends Model
{
    protected $table = 'riwayat_kelas';

    protected $primaryKey = 'id_riwayat_kelas';

    protected $fillable = ['id_siswa', 'id_kelas', 'id_semester'];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'id_siswa', 'id_siswa');
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'id_kelas', 'id_kelas');
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class, 'id_semester', 'id_semester');
    }

    public function siswaRombel(): HasMany
    {
        return $this->hasMany(SiswaRombel::class, 'id_riwayat_kelas', 'id_riwayat_kelas');
    }

    public function mutasi(): HasMany
    {
        return $this->hasMany(MutasiKelas::class, 'id_riwayat_kelas', 'id_riwayat_kelas');
    }
}
