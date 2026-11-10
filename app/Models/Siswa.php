<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Profil siswa. Tabel: siswa. PK: id_siswa.
 */
class Siswa extends Model
{
    protected $table = 'siswa';

    protected $primaryKey = 'id_siswa';

    public $timestamps = false;

    protected $fillable = ['id_user', 'nis', 'nisn', 'nama', 'agama', 'jenis_kelamin', 'tahun_masuk', 'urutan_masuk', 'alamat'];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function riwayatKelas()
    {
        return $this->hasMany(RiwayatKelas::class, 'id_siswa', 'id_siswa');
    }

    /**
     * Riwayat kelas aktif siswa (semester aktif).
     */
    public function riwayatKelasAktif()
    {
        return $this->hasOne(RiwayatKelas::class, 'id_siswa', 'id_siswa')
            ->whereHas('semester', fn ($q) => $q->where('aktif', true));
    }
}
