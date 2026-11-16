<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Penugasan guru BK per tingkat per semester.
 * Tabel: penugasan_bk. PK: id_penugasan_bk.
 */
class PenugasanBk extends Model
{
    protected $table = 'penugasan_bk';

    protected $primaryKey = 'id_penugasan_bk';

    public $timestamps = false;

    protected $fillable = [
        'id_guru',
        'id_semester',
        'tingkat',
        'jadwal_layanan',
    ];

    protected $casts = [
        'jadwal_layanan' => 'string',
    ];

    public function guru()
    {
        return $this->belongsTo(Guru::class, 'id_guru', 'id_guru');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'id_semester', 'id_semester');
    }
}
