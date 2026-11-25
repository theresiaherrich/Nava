<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PelengkapRapor extends Model
{
    protected $table = 'pelengkap_rapor';

    protected $primaryKey = 'id_pelengkap_rapor';

    protected $fillable = [
        'id_riwayat_kelas', 'id_periode', 'sakit', 'izin', 'alpha', 'sumber_presensi',
        'catatan_wali', 'kokurikuler', 'diisi_oleh',
    ];

    protected function casts(): array
    {
        return ['sakit' => 'integer', 'izin' => 'integer', 'alpha' => 'integer'];
    }
}
