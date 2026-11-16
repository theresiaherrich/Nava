<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** pengajuan_nilai (ERD): nilai satu rombel × periode × kelas asal. */
class PaketNilai extends Model
{
    public const STATUS = ['draf', 'terkirim', 'perlu_revisi', 'disetujui', 'dikunci'];

    /** Status yang masih boleh diubah guru. */
    public const BISA_DIEDIT = ['draf', 'perlu_revisi'];

    protected $table = 'paket_nilai';

    protected $primaryKey = 'id_paket_nilai';

    protected $fillable = [
        'id_rombel', 'id_periode', 'id_kelas', 'status', 'lengkap', 'koreksi', 'alasan_koreksi',
        'dikirim_oleh', 'waktu_kirim', 'diverifikasi_oleh', 'waktu_verifikasi', 'dikunci_oleh', 'waktu_kunci',
    ];

    protected function casts(): array
    {
        return [
            'lengkap' => 'boolean',
            'koreksi' => 'boolean',
            'waktu_kirim' => 'datetime',
            'waktu_verifikasi' => 'datetime',
            'waktu_kunci' => 'datetime',
        ];
    }

    public function rombel(): BelongsTo
    {
        return $this->belongsTo(Rombel::class, 'id_rombel', 'id_rombel');
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(PeriodeNilai::class, 'id_periode', 'id_periode');
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'id_kelas', 'id_kelas');
    }

    public function riwayat(): HasMany
    {
        return $this->hasMany(VerifikasiNilai::class, 'id_paket_nilai', 'id_paket_nilai')->orderBy('id_verifikasi_nilai');
    }

    public function bisaDiedit(): bool
    {
        return in_array($this->status, self::BISA_DIEDIT, true);
    }
}
