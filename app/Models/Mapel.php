<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Master mata pelajaran. Tabel: mapel. PK: id_mapel.
 * Kolom RIASEC sudah ada langsung di tabel ini (tidak perlu profil_mapel).
 */
class Mapel extends Model
{
    protected $table = 'mapel';

    protected $primaryKey = 'id_mapel';

    public $timestamps = false;

    protected $fillable = [
        'id_dimensi',
        'kode',
        'nama',
        'rumpun',
        'deskripsi',
        'is_pilihan',       // 1 = mapel pilihan (dipakai untuk rekomendasi kelas X)
        'kode_riasec',      // 3-huruf Holland, misal 'RIS'
        'aktif',
        'status_validasi',  // 'draft' | 'diajukan' | 'tervalidasi' | 'ditolak'
        'catatan_validasi',
        'divalidasi_oleh',
        'waktu_validasi',
    ];

    protected $casts = [
        'is_pilihan'     => 'boolean',
        'aktif'          => 'boolean',
        'waktu_validasi' => 'datetime',
    ];

    // ─── Relasi ─────────────────────────────────────────────────────────────

    public function rekomendasiSepuluh()
    {
        return $this->hasMany(RekomendasiSepuluh::class, 'id_mapel', 'id_mapel');
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'divalidasi_oleh', 'id_user');
    }

    // ─── Scopes ─────────────────────────────────────────────────────────────

    /** Hanya mapel pilihan (is_pilihan = 1). */
    public function scopePilihan($query)
    {
        return $query->where('is_pilihan', 1);
    }

    /** Hanya mapel aktif. */
    public function scopeAktif($query)
    {
        return $query->where('aktif', 1);
    }

    /** Hanya mapel yang sudah tervalidasi. */
    public function scopeTervalidasi($query)
    {
        return $query->where('status_validasi', 'tervalidasi');
    }
}
