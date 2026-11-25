<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Siswa extends Model
{
    public const STATUS = ['aktif', 'lulus', 'pindah', 'keluar'];

    protected $table = 'siswa';

    protected $primaryKey = 'id_siswa';

    protected $fillable = [
        'nis', 'nisn', 'nama', 'jenis_kelamin', 'id_agama', 'tempat_lahir', 'tanggal_lahir',
        'alamat', 'tahun_masuk', 'status',
        // Dipakai tambah/impor siswa modul A1 (Presensi).
        'id_user', 'urutan_masuk', 'agama',
    ];

    protected function casts(): array
    {
        return ['tanggal_lahir' => 'date:Y-m-d'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function agama(): BelongsTo
    {
        return $this->belongsTo(Agama::class, 'id_agama', 'id_agama');
    }

    /**
     * Modul A1 mengirim agama sebagai teks (mis. "Islam"). Skema A2 menyimpannya sebagai
     * id_agama, jadi teks dipetakan ke tabel agama. $siswa->agama tetap relasi Agama.
     */
    public function setAgamaAttribute(?string $nama): void
    {
        $nama = trim((string) $nama);

        $this->attributes['id_agama'] = $nama === ''
            ? null
            : Agama::whereRaw('LOWER(nama) = ?', [mb_strtolower($nama)])->value('id_agama');
    }

    public function riwayatKelas(): HasMany
    {
        return $this->hasMany(RiwayatKelas::class, 'id_siswa', 'id_siswa');
    }

    /** Data orang tua dari pendaftaran mandiri (modul A1/A4), kolom orang_tua.id_siswa. */
    public function dataOrangTua(): HasMany
    {
        return $this->hasMany(OrangTua::class, 'id_siswa', 'id_siswa');
    }

    public function presensi(): HasMany
    {
        return $this->hasMany(Presensi::class, 'id_siswa', 'id_siswa');
    }

    public function qrSiswa(): HasOne
    {
        return $this->hasOne(QrSiswa::class, 'id_siswa', 'id_siswa');
    }

    public function orangTua(): BelongsToMany
    {
        return $this->belongsToMany(OrangTua::class, 'orang_tua_siswa', 'id_siswa', 'id_orang_tua')
            ->withPivot('hubungan')->withTimestamps();
    }
}
