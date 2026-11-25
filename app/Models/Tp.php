<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Tujuan Pembelajaran. Maksimal 10 per rombel (pengajaran per semester). */
class Tp extends Model
{
    public const MAKS_PER_ROMBEL = 10;

    protected $table = 'tp';

    protected $primaryKey = 'id_tp';

    protected $fillable = ['id_rombel', 'urutan', 'kode', 'deskripsi', 'kktp', 'hanya_sts'];

    protected function casts(): array
    {
        return ['urutan' => 'integer', 'kktp' => 'float', 'hanya_sts' => 'boolean'];
    }

    public function rombel(): BelongsTo
    {
        return $this->belongsTo(Rombel::class, 'id_rombel', 'id_rombel');
    }

    public function nilai(): HasMany
    {
        return $this->hasMany(NilaiTp::class, 'id_tp', 'id_tp');
    }

    public function kktpEfektif(?Mapel $mapel = null): float
    {
        return $this->kktp ?? $mapel?->kktp_default ?? (float) config('siakad.kktp_default');
    }
}
