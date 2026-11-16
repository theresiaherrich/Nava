<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MutasiKelas extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'mutasi_kelas';

    protected $primaryKey = 'id_mutasi_kelas';

    protected $fillable = ['id_riwayat_kelas', 'dari_kelas', 'ke_kelas', 'alasan', 'dipindah_oleh'];
}
