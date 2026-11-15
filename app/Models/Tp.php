<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tp extends Model
{
    protected $table = 'tp';

    protected $fillable = ['rombel_id', 'nomor_urut', 'deskripsi', 'masuk_sts', 'is_sas'];

    protected function casts(): array
    {
        return ['masuk_sts' => 'boolean', 'is_sas' => 'boolean', 'nomor_urut' => 'integer'];
    }

    public function rombel(): BelongsTo
    {
        return $this->belongsTo(Rombel::class, 'rombel_id');
    }

    public function nilai(): HasMany
    {
        return $this->hasMany(NilaiTp::class, 'tp_id');
    }
}
