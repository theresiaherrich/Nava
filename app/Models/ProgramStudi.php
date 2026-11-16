<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Master program studi. Tabel: program_studi. PK: id_prodi.
 */
class ProgramStudi extends Model
{
    protected $table = 'program_studi';

    protected $primaryKey = 'id_prodi';

    public $timestamps = false;

    protected $fillable = [
        'id_dimensi',
        'nama',
        'rumpun',
        'tentang',
        'deskripsi',
        'kode_riasec',      // Ganti dari kode_holland → kode_riasec
        'aktif',
        'status_validasi',  // 'draft' | 'diajukan' | 'tervalidasi' | 'ditolak'
        'catatan_validasi',
        'divalidasi_oleh',
        'waktu_validasi',
    ];

    protected $casts = [
        'aktif'          => 'boolean',
        'waktu_validasi' => 'datetime',
    ];

    // ─── Relasi ─────────────────────────────────────────────────────────────

    /**
     * Profesi terkait prodi.
     * Pivot: prodi_profesi (bukan profesi_prodi — sesuai DB siakad_sma1).
     */
    public function profesi()
    {
        return $this->belongsToMany(Profesi::class, 'prodi_profesi', 'id_prodi', 'id_profesi');
    }

    public function rekomendasiDuaBelas()
    {
        return $this->hasMany(RekomendasiDuaBelas::class, 'id_prodi', 'id_prodi');
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'divalidasi_oleh', 'id_user');
    }

    // ─── Scopes ─────────────────────────────────────────────────────────────

    public function scopeAktif($query)
    {
        return $query->where('aktif', 1);
    }

    public function scopeTervalidasi($query)
    {
        return $query->where('status_validasi', 'tervalidasi');
    }
}
