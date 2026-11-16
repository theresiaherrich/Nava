<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** periode_penilaian (ERD). Satu STS dan satu SAS per semester. */
class PeriodeNilai extends Model
{
    public const JENIS = ['STS', 'SAS'];

    protected $table = 'periode_nilai';

    protected $primaryKey = 'id_periode';

    protected $fillable = ['id_semester', 'jenis', 'tgl_buka', 'tgl_tutup', 'status'];

    protected $with = ['tingkatPeserta'];

    protected $hidden = ['tingkatPeserta'];

    protected $appends = ['status_efektif', 'tingkat'];

    protected function casts(): array
    {
        return ['tgl_buka' => 'datetime', 'tgl_tutup' => 'datetime'];
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class, 'id_semester', 'id_semester');
    }

    public function tingkatPeserta(): HasMany
    {
        return $this->hasMany(PeriodeTingkat::class, 'id_periode', 'id_periode');
    }

    public function paket(): HasMany
    {
        return $this->hasMany(PaketNilai::class, 'id_periode', 'id_periode');
    }

    /** @return list<string> tingkat yang ikut periode ini, urut X, XI, XII. */
    public function getTingkatAttribute(): array
    {
        return $this->tingkatPeserta->pluck('tingkat')
            ->sortBy(fn ($t) => array_search($t, Kelas::TINGKAT, true))
            ->values()->all();
    }

    public function ikutTingkat(string $tingkat): bool
    {
        return in_array($tingkat, $this->tingkat, true);
    }

    /**
     * Status yang dilihat pengguna: belum_diatur | belum_dibuka | dibuka | ditutup | dikunci.
     * "ditutup" tidak disimpan; diturunkan dari tgl_tutup yang sudah lewat.
     */
    public function getStatusEfektifAttribute(): string
    {
        if ($this->status === 'dikunci') {
            return 'dikunci';
        }
        if ($this->status === 'belum_diatur' || $this->tgl_buka === null || $this->tgl_tutup === null) {
            return 'belum_diatur';
        }
        if (now()->lt($this->tgl_buka)) {
            return 'belum_dibuka';
        }

        return now()->gt($this->tgl_tutup) ? 'ditutup' : 'dibuka';
    }

    public function terbukaUntukInput(): bool
    {
        return $this->status_efektif === 'dibuka';
    }
}
