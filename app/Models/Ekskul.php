<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ekskul extends Model
{
    protected $table = 'ekskul';

    protected $primaryKey = 'id_ekskul';

    protected $fillable = ['nama', 'id_pembina', 'aktif'];

    protected function casts(): array
    {
        return ['aktif' => 'boolean'];
    }

    public function pembina(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'id_pembina', 'id_guru');
    }

    public function peserta(): HasMany
    {
        return $this->hasMany(PesertaEkskul::class, 'id_ekskul', 'id_ekskul');
    }
}
