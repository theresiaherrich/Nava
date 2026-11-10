<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Profil guru. Tabel: guru. PK: id_guru.
 */
class Guru extends Model
{
    protected $table = 'guru';

    protected $primaryKey = 'id_guru';

    public $timestamps = false;

    protected $fillable = ['id_user', 'nip', 'nama', 'status_kepegawaian', 'agama', 'jenis_kelamin', 'alamat'];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function guruBk()
    {
        return $this->hasOne(GuruBk::class, 'id_guru', 'id_guru');
    }
}
