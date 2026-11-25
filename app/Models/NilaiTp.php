<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NilaiTp extends Model
{
    protected $table = 'nilai_tp';

    protected $primaryKey = 'id_nilai_tp';

    protected $fillable = ['id_tp', 'id_siswa_rombel', 'id_periode', 'nilai', 'diubah_oleh'];

    protected function casts(): array
    {
        return ['nilai' => 'float'];
    }

    public function tp(): BelongsTo
    {
        return $this->belongsTo(Tp::class, 'id_tp', 'id_tp');
    }

    public function siswaRombel(): BelongsTo
    {
        return $this->belongsTo(SiswaRombel::class, 'id_siswa_rombel', 'id_siswa_rombel');
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(PeriodeNilai::class, 'id_periode', 'id_periode');
    }
}
