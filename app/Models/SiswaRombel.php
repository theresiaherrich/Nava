<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SiswaRombel extends Model
{
    protected $table = 'siswa_rombel';

    protected $primaryKey = 'id_siswa_rombel';

    protected $fillable = ['id_rombel', 'id_riwayat_kelas'];

    public function rombel(): BelongsTo
    {
        return $this->belongsTo(Rombel::class, 'id_rombel', 'id_rombel');
    }

    public function riwayatKelas(): BelongsTo
    {
        return $this->belongsTo(RiwayatKelas::class, 'id_riwayat_kelas', 'id_riwayat_kelas');
    }

    public function nilai(): HasMany
    {
        return $this->hasMany(NilaiTp::class, 'id_siswa_rombel', 'id_siswa_rombel');
    }
}
