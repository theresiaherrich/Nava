<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rombel extends Model
{
    protected $table = 'rombel';

    protected $primaryKey = 'id_rombel';

    protected $fillable = [
        'id_semester', 'id_mapel', 'nama', 'tingkat', 'jam', 'kelompok_rapor', 'metode_nilai',
    ];

    public function mapel(): BelongsTo
    {
        return $this->belongsTo(Mapel::class, 'id_mapel', 'id_mapel');
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class, 'id_semester', 'id_semester');
    }

    public function guru(): BelongsToMany
    {
        return $this->belongsToMany(Guru::class, 'guru_rombel', 'id_rombel', 'id_guru');
    }

    public function paket(): HasMany
    {
        return $this->hasMany(PaketNilai::class, 'id_rombel', 'id_rombel');
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
