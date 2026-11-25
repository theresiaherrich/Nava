<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kelas extends Model
{
    public const TINGKAT = ['X', 'XI', 'XII'];

    protected $table = 'kelas';

    protected $primaryKey = 'id_kelas';

    protected $fillable = ['id_tahun_ajaran', 'nama', 'tingkat', 'fase'];

    public static function faseUntuk(string $tingkat): string
    {
        return $tingkat === 'X' ? 'E' : 'F';
    }

    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class, 'id_tahun_ajaran', 'id_tahun_ajaran');
    }

    public function anggota(): HasMany
    {
        return $this->hasMany(RiwayatKelas::class, 'id_kelas', 'id_kelas');
    }

    public function wali(): HasMany
    {
        return $this->hasMany(WaliKelas::class, 'id_kelas', 'id_kelas');
    }

    /** Nama relasi versi modul A1 (Presensi); sama dengan anggota(). */
    public function riwayatKelas(): HasMany
    {
        return $this->anggota();
    }

    /** Nama relasi versi modul A1 (Presensi); sama dengan wali(). */
    public function waliKelas(): HasMany
    {
        return $this->wali();
    }
}
