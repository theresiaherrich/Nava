<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Jadwal tes RIASEC per semester dan jenjang. Tabel: periode_tes. PK: id_periode_tes.
 */
class PeriodeTes extends Model
{
    protected $table = 'periode_tes';

    protected $primaryKey = 'id_periode_tes';

    public $timestamps = false;

    protected $fillable = ['id_semester', 'nama', 'tingkat', 'tanggal_mulai', 'tanggal_selesai', 'status'];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'id_semester', 'id_semester');
    }

    /**
     * Cari periode aktif untuk semester dan tingkat tertentu.
     */
    public static function cariAktif(int $idSemester, string $tingkat): ?static
    {
        return static::where('id_semester', $idSemester)
            ->where('tingkat', $tingkat)
            ->where('status', 'aktif')
            ->first();
    }
}
