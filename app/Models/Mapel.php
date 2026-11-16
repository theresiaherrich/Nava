<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mapel extends Model
{
    protected $table = 'mapel';

    protected $primaryKey = 'id_mapel';

    protected $fillable = ['kode', 'nama', 'kelompok', 'aktif'];
    protected $fillable = ['kode', 'nama', 'aktif'];

    protected function casts(): array
    {
        return ['aktif' => 'boolean'];
    }

    public function rombel(): HasMany
    {
        return $this->hasMany(Rombel::class, 'id_mapel', 'id_mapel');
    }
}
