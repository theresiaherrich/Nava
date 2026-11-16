<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class OrangTua extends Model
{
    protected $table = 'orang_tua';

    protected $primaryKey = 'id_orang_tua';

    protected $fillable = ['nama', 'jenis_kelamin', 'no_telepon', 'alamat'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function anak(): BelongsToMany
    {
        return $this->belongsToMany(Siswa::class, 'orang_tua_siswa', 'id_orang_tua', 'id_siswa')
            ->withPivot('hubungan')->withTimestamps();
    }
}
