<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rombel extends Model
{
    protected $table = 'rombel';

    protected $fillable = ['semester_id', 'mapel_id', 'nama'];

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function mapel(): BelongsTo
    {
        return $this->belongsTo(Mapel::class);
    }

    public function gurus(): BelongsToMany
    {
        return $this->belongsToMany(Guru::class, 'guru_rombel', 'rombel_id', 'guru_id')->withTimestamps();
    }

    public function peserta(): HasMany
    {
        return $this->hasMany(SiswaRombel::class, 'rombel_id');
    }

    public function tp(): HasMany
    {
        return $this->hasMany(Tp::class, 'rombel_id')->orderBy('nomor_urut');
    }

    public function dapatDiampuOleh(?int $guruId): bool
    {
        return $guruId !== null && $this->gurus()->whereKey($guruId)->exists();
    }
}
