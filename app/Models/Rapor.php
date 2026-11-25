<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rapor extends Model
{
    protected $table = 'rapor';

    protected $primaryKey = 'id_rapor';

    protected $fillable = [
        'id_riwayat_kelas', 'id_periode', 'id_template', 'versi', 'data_final', 'file_pdf', 'status',
        'dicetak_oleh', 'diterbitkan_oleh', 'waktu_terbit', 'alasan_cabut',
    ];

    // Path file privat dan snapshot tidak dikirim di daftar; unduh lewat endpoint berizin.
    protected $hidden = ['file_pdf', 'data_final'];

    protected function casts(): array
    {
        return ['data_final' => 'array', 'waktu_terbit' => 'datetime', 'versi' => 'integer'];
    }

    public function riwayatKelas(): BelongsTo
    {
        return $this->belongsTo(RiwayatKelas::class, 'id_riwayat_kelas', 'id_riwayat_kelas');
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(PeriodeNilai::class, 'id_periode', 'id_periode');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(TemplateRapor::class, 'id_template', 'id_template');
    }
}
