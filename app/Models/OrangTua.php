<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Primary key komposit (id_siswa, hubungan). Jangan memakai $model->save() untuk
 * mengubah baris; gunakan query builder yang memfilter kedua kolom (lihat SiswaController).
 */
class OrangTua extends Model
{
    protected $table = 'orang_tua';

    protected $primaryKey = 'id_siswa';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'id_siswa', 'hubungan', 'id_user', 'alamat', 'no_telepon', 'jenis_kelamin',
        'nama', 'status_verifikasi', 'diverifikasi_oleh', 'waktu_verifikasi',
    ];

    protected function casts(): array
    {
        return ['waktu_verifikasi' => 'datetime'];
    }
}
