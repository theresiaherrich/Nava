<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PeriodeNilai extends Model
{
    protected $table = 'periode_nilai';

    protected $primaryKey = 'id_periode';

    protected $fillable = [
        'id_semester', 'tingkat', 'jenis', 'tanggal_mulai', 'tanggal_selesai', 'status',
    ];

    protected function casts(): array
    {
        return ['tanggal_mulai' => 'date', 'tanggal_selesai' => 'date'];
    protected $fillable = ['semester_id', 'jenis', 'tingkat', 'tanggal_buka', 'tanggal_tutup', 'dikunci'];

    protected function casts(): array
    {
        return ['tingkat' => 'integer', 'tanggal_buka' => 'datetime', 'tanggal_tutup' => 'datetime', 'dikunci' => 'boolean'];
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class, 'id_semester', 'id_semester');
    }

    public function paket(): HasMany
    {
        return $this->hasMany(PaketNilai::class, 'id_periode', 'id_periode');
        return $this->belongsTo(Semester::class);
    }

    public function terbuka(): bool
    {
        return ! $this->dikunci && now()->between($this->tanggal_buka, $this->tanggal_tutup);
    }
}
