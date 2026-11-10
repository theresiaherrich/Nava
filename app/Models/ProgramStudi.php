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
        'nama', 'kategori', 'deskripsi', 'kode_holland',
        'status_validasi', 'catatan_validasi', 'divalidasi_oleh', 'waktu_validasi',
    ];

    protected $casts = [
        'waktu_validasi' => 'datetime',
    ];

    public function profesi()
    {
        return $this->belongsToMany(Profesi::class, 'profesi_prodi', 'id_prodi', 'id_profesi');
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'divalidasi_oleh', 'id_user');
    }
}
