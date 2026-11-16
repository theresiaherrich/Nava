<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kelas extends Model
{
    protected $table = 'kelas';

    protected $primaryKey = 'id_kelas';

    protected $fillable = ['nama', 'tingkat', 'fase', 'id_tahun_ajaran'];

    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class, 'id_tahun_ajaran', 'id_tahun_ajaran');
    }

    public function riwayat(): HasMany
    {
        return $this->hasMany(RiwayatKelas::class, 'id_kelas', 'id_kelas');
    }

    public function waliKelas(): HasMany
    {
        return $this->hasMany(WaliKelas::class, 'id_kelas', 'id_kelas');
    protected $fillable = ['semester_id', 'nama', 'tingkat', 'fase'];

    protected function casts(): array
    {
        return ['tingkat' => 'integer'];
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function siswaKelas(): HasMany
    {
        return $this->hasMany(SiswaKelas::class);
    }
}
