<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Profil guru. Tabel: guru. PK: id_guru.
 * Guru BK tidak lagi punya tabel guru_bk — info BK ada di penugasan_bk.
 */
class Guru extends Model
{
    protected $table = 'guru';

    protected $primaryKey = 'id_guru';

    public $timestamps = false;

    protected $fillable = [
        'id_user',
        'nip',
        'nama',
        'email',
        'no_telepon',
        'status_kepegawaian',
        'agama',
        'jenis_kelamin',
        'alamat',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    /**
     * Semua penugasan BK guru ini (berbeda semester / tingkat).
     */
    public function penugasanBk()
    {
        return $this->hasMany(PenugasanBk::class, 'id_guru', 'id_guru');
    }

    /**
     * Penugasan BK aktif pada semester tertentu.
     */
    public function penugasanBkAktif(int $idSemester)
    {
        return $this->penugasanBk()->where('id_semester', $idSemester)->get();
    }
}
