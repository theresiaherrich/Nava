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

    protected $fillable = ['nip', 'status_kepegawaian', 'nama', 'jenis_kelamin', 'id_agama', 'no_telepon', 'aktif'];

    protected function casts(): array
    {
        return ['aktif' => 'boolean'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function rombel(): BelongsToMany
    {
        return $this->belongsToMany(Rombel::class, 'guru_rombel', 'id_guru', 'id_rombel')->withTimestamps();
    }

    public function waliKelas(): HasMany
    {
        return $this->hasMany(WaliKelas::class, 'id_guru', 'id_guru');
    }
}
