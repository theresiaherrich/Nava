<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Guru extends Model
{
    protected $table = 'guru';

    protected $primaryKey = 'id_guru';

    public $timestamps = false;

    protected $fillable = [
        'id_user',
        'nip',
        'status_kepegawaian',
        'nama',
        'agama',
        'jenis_kelamin',
        'alamat',
        'email',
        'no_telepon',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function waliKelas(): HasMany
    {
        return $this->hasMany(WaliKelas::class, 'id_guru', 'id_guru');
    }
}
