<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Sesi tes RIASEC — satu baris per pengerjaan tes.
 * Tabel: sesi_tes. PK: id_sesi_tes.
 */
class SesiTes extends Model
{
    protected $table = 'sesi_tes';

    protected $primaryKey = 'id_sesi_tes';

    public $timestamps = false;

    protected $fillable = [
        'id_riwayat_kelas',
        'tingkat',
        'kode_holland',
        'waktu_mulai',
        'waktu_selesai',
        'status',              // 'berlangsung' | 'selesai'
        'diverifikasi_oleh',
        'waktu_verifikasi',
    ];

    protected $casts = [
        'waktu_mulai'       => 'datetime',
        'waktu_selesai'     => 'datetime',
        'waktu_verifikasi'  => 'datetime',
    ];

    // Accessor / Mutator untuk alias kode_dominan -> kode_holland
    public function getKodeDominanAttribute()
    {
        return $this->attributes['kode_holland'] ?? null;
    }

    public function setKodeDominanAttribute($value)
    {
        $this->attributes['kode_holland'] = $value;
    }

    // ─── Relasi ─────────────────────────────────────────────────────────────

    public function riwayatKelas()
    {
        return $this->belongsTo(RiwayatKelas::class, 'id_riwayat_kelas', 'id_riwayat_kelas');
    }

    public function hasilTes()
    {
        return $this->hasMany(HasilTesRiasec::class, 'id_sesi_tes', 'id_sesi_tes');
    }

    public function jawaban()
    {
        return $this->hasMany(JawabanRiasec::class, 'id_sesi_tes', 'id_sesi_tes');
    }

    public function rekomendasiSepuluh()
    {
        return $this->hasMany(RekomendasiSepuluh::class, 'id_sesi_tes', 'id_sesi_tes');
    }

    public function rekomendasiDuaBelas()
    {
        return $this->hasMany(RekomendasiDuaBelas::class, 'id_sesi_tes', 'id_sesi_tes');
    }

    public function verifikator()
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh', 'id_user');
    }

    // ─── Scopes ─────────────────────────────────────────────────────────────

    public function scopeSelesai($query)
    {
        return $query->where('status', 'selesai');
    }

    public function scopeBerlangsung($query)
    {
        return $query->where('status', 'berlangsung');
    }

    // ─── Helper ─────────────────────────────────────────────────────────────

    public function skorPerDimensi(): array
    {
        return $this->hasilTes
            ->mapWithKeys(fn ($h) => [$h->dimensi->kode ?? '?' => $h->skor_normal])
            ->toArray();
    }
}
