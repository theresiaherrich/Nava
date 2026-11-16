<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sekolah extends Model
{
    protected $table = 'sekolah';

    protected $primaryKey = 'id_sekolah';

    protected $fillable = ['npsn', 'nama', 'alamat', 'kota', 'telepon', 'email', 'kepala_sekolah', 'nip_kepala_sekolah'];
}
