<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PelengkapRapor extends Model
{
    protected $table = 'pelengkap_rapor';

    protected $primaryKey = 'id_pelengkap_rapor';

    /** Kolom sakit/izin/alpha/id_presensi milik A1: sengaja tidak fillable, A2 tidak menulisnya. */
    protected $fillable = ['id_riwayat_kelas', 'id_periode', 'catatan_wali', 'kokurikuler'];

    public function siswaKelas(): BelongsTo
    {
        return $this->belongsTo(SiswaKelas::class, 'id_riwayat_kelas');
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(PeriodeNilai::class, 'id_periode');
    }
}
