<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Riwayat kelas siswa per semester. Tabel: riwayat_kelas. PK: id_riwayat_kelas.
 */
class RiwayatKelas extends Model
{
    protected $table = 'riwayat_kelas';

    protected $primaryKey = 'id_riwayat_kelas';

    public $timestamps = false;

    protected $fillable = ['id_siswa', 'id_kelas', 'id_semester'];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'id_siswa', 'id_siswa');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'id_kelas', 'id_kelas');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'id_semester', 'id_semester');
    }

    public function sesiTes()
    {
        return $this->hasOne(SesiTes::class, 'id_riwayat_kelas', 'id_riwayat_kelas');
    }

    public function pengajuanBk()
    {
        return $this->hasMany(PengajuanBk::class, 'id_riwayat_kelas', 'id_riwayat_kelas');
    }
}
