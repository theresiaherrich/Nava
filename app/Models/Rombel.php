<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Pengajaran (ERD): satu mapel pada satu semester, boleh berisi siswa dari beberapa kelas. */
class Rombel extends Model
{
    protected $table = 'rombel';

    protected $primaryKey = 'id_rombel';

    protected $fillable = ['id_semester', 'id_mapel', 'nama'];

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class, 'id_semester', 'id_semester');
    }

    public function mapel(): BelongsTo
    {
        return $this->belongsTo(Mapel::class, 'id_mapel', 'id_mapel');
    }

    public function guru(): BelongsToMany
    {
        return $this->belongsToMany(Guru::class, 'guru_rombel', 'id_rombel', 'id_guru')->withTimestamps();
    }

    public function peserta(): HasMany
    {
        return $this->hasMany(SiswaRombel::class, 'id_rombel', 'id_rombel');
    }

    public function tp(): HasMany
    {
        return $this->hasMany(Tp::class, 'id_rombel', 'id_rombel')->orderBy('urutan');
    }

    public function paket(): HasMany
    {
        return $this->hasMany(PaketNilai::class, 'id_rombel', 'id_rombel');
    }
}
