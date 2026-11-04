<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sekolah extends Model
{
    protected $table = 'sekolah';

    protected $fillable = ['npsn', 'nama', 'alamat', 'nama_kepala_sekolah'];

    protected function casts(): array
    {
        return [];
    }
}
