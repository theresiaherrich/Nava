<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Guru BK — perpanjangan dari guru, berisi kontak dan jadwal layanan.
 * Tabel: guru_bk. PK: id_guru (FK ke guru).
 */
class GuruBk extends Model
{
    protected $table = 'guru_bk';

    protected $primaryKey = 'id_guru';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['id_guru', 'email', 'no_telepon', 'tingkat', 'jadwal_layanan', 'aktif'];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    public function guru()
    {
        return $this->belongsTo(Guru::class, 'id_guru', 'id_guru');
    }

    public function pengajuanBk()
    {
        return $this->hasMany(PengajuanBk::class, 'id_guru', 'id_guru');
    }
}
