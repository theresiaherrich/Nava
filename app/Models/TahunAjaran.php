<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TahunAjaran extends Model
{
    protected $table = 'tahun_ajaran';

    protected $primaryKey = 'id_tahun_ajaran';

    protected $fillable = ['nama', 'aktif'];

    protected function casts(): array
    {
        return ['aktif' => 'boolean'];
    }

    public function semester(): HasMany
    {
        return $this->hasMany(Semester::class, 'id_tahun_ajaran', 'id_tahun_ajaran');
    }

    public function kelas(): HasMany
    {
        return $this->hasMany(Kelas::class, 'id_tahun_ajaran', 'id_tahun_ajaran');
    public function semesters(): HasMany
    {
        return $this->hasMany(Semester::class, 'tahun_ajaran_id');
    }
}
