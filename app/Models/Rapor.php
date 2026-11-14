<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rapor extends Model
{
    protected $table = 'rapor';

    protected $primaryKey = 'id_rapor';

    protected $fillable = [
        'id_riwayat_kelas', 'id_periode', 'id_template', 'versi', 'data_final', 'file_pdf',
        'tanggal_cetak', 'status', 'diterbitkan_oleh', 'waktu_terbit',
    ];

    protected function casts(): array
    {
        return ['data_final' => 'array', 'tanggal_cetak' => 'date', 'waktu_terbit' => 'datetime'];
    }

    public function siswaKelas(): BelongsTo
    {
        return $this->belongsTo(SiswaKelas::class, 'id_riwayat_kelas');
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(PeriodeNilai::class, 'id_periode');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(TemplateRapor::class, 'id_template', 'id_template');
    }

    public function pencetak(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diterbitkan_oleh');
    }
}
