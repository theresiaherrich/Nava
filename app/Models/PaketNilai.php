<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Paket nilai = satu rombel + satu periode + satu kelas asal.
 * Status: draf -> terkirim -> (revisi | disetujui) -> terkunci.
 * Nilai hanya boleh diubah saat status draf atau revisi (lihat isEditable).
 */
class PaketNilai extends Model
{
    protected $table = 'paket_nilai';

    protected $primaryKey = 'id_paket_nilai';

    protected $fillable = [
        'id_periode', 'id_kelas', 'id_rombel', 'status', 'dikirim_oleh', 'waktu_kirim',
        'dikunci_oleh', 'waktu_kunci', 'alasan_koreksi',
    ];

    protected function casts(): array
    {
        return ['waktu_kirim' => 'datetime', 'waktu_kunci' => 'datetime'];
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(PeriodeNilai::class, 'id_periode', 'id_periode');
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'id_kelas', 'id_kelas');
    }

    public function rombel(): BelongsTo
    {
        return $this->belongsTo(Rombel::class, 'id_rombel', 'id_rombel');
    }

    public function isEditable(): bool
    {
        return in_array($this->status, ['draf', 'revisi'], true);
    }
}
