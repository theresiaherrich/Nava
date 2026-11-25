<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** nilai_ekskul (ERD). Predikat hanya dicetak di rapor semester (SAS). */
class PesertaEkskul extends Model
{
    public const PREDIKAT = ['SB', 'B', 'K'];

    protected $table = 'peserta_ekskul';

    protected $primaryKey = 'id_peserta_ekskul';

    protected $fillable = ['id_ekskul', 'id_riwayat_kelas', 'predikat', 'catatan', 'diisi_oleh'];

    public function ekskul(): BelongsTo
    {
        return $this->belongsTo(Ekskul::class, 'id_ekskul', 'id_ekskul');
    }

    public function riwayatKelas(): BelongsTo
    {
        return $this->belongsTo(RiwayatKelas::class, 'id_riwayat_kelas', 'id_riwayat_kelas');
    }
}
