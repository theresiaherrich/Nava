<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PesertaEkskul extends Model
{
    protected $table = 'peserta_ekskul';

    protected $primaryKey = 'id_peserta_ekskul';

    protected $fillable = ['id_ekskul', 'id_siswa', 'id_semester', 'id_predikat', 'catatan', 'diisi_oleh'];

    public function ekskul(): BelongsTo
    {
        return $this->belongsTo(Ekskul::class, 'id_ekskul', 'id_ekskul');
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'id_siswa');
    }

    public function predikat(): BelongsTo
    {
        return $this->belongsTo(Predikat::class, 'id_predikat', 'id_predikat');
    }
}
