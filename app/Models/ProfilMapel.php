<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Profil mapel pilihan (kode Holland 3 huruf + status validasi BK).
 * Tabel: profil_mapel. PK: id_mapel (sama dengan mapel).
 */
class ProfilMapel extends Model
{
    protected $table = 'profil_mapel';

    protected $primaryKey = 'id_mapel';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'id_mapel', 'rumpun', 'deskripsi', 'kode_holland',
        'status_validasi', 'catatan_validasi', 'divalidasi_oleh', 'waktu_validasi',
    ];

    protected $casts = [
        'waktu_validasi' => 'datetime',
    ];

    public function mapel()
    {
        return $this->belongsTo(Mapel::class, 'id_mapel', 'id_mapel');
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'divalidasi_oleh', 'id_user');
    }
}
