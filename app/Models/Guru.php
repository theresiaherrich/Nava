<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Guru extends Model
{
    protected $table = 'guru';

    protected $primaryKey = 'id_guru';

    protected $fillable = [
        'id_user', 'nip', 'status_kepegawaian', 'nama', 'agama', 'jenis_kelamin', 'alamat',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function waliKelas(): HasMany
    {
        return $this->hasMany(WaliKelas::class, 'id_guru', 'id_guru');
    }

    public function rombel(): BelongsToMany
    {
        return $this->belongsToMany(Rombel::class, 'guru_rombel', 'id_guru', 'id_rombel');
    }
}
