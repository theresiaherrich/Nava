<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Hasil tes RIASEC — satu baris per siswa per semester.
 * Tabel: sesi_tes. PK: id_sesi_tes.
 * Skor: 0-100 per dimensi (jumlah jawaban / (n_soal_dimensi * 5) * 100).
 */
class SesiTes extends Model
{
    protected $table = 'sesi_tes';

    protected $primaryKey = 'id_sesi_tes';

    public $timestamps = false;

    protected $fillable = [
        'id_riwayat_kelas',
        'skor_r', 'skor_i', 'skor_a', 'skor_s', 'skor_e', 'skor_c',
        'kode_holland', 'waktu_selesai',
    ];

    protected $casts = [
        'waktu_selesai' => 'datetime',
        'skor_r' => 'float',
        'skor_i' => 'float',
        'skor_a' => 'float',
        'skor_s' => 'float',
        'skor_e' => 'float',
        'skor_c' => 'float',
    ];

    public function riwayatKelas()
    {
        return $this->belongsTo(RiwayatKelas::class, 'id_riwayat_kelas', 'id_riwayat_kelas');
    }

    /**
     * Kembalikan array skor per kode dimensi.
     */
    public function skorPerDimensi(): array
    {
        return [
            'R' => $this->skor_r,
            'I' => $this->skor_i,
            'A' => $this->skor_a,
            'S' => $this->skor_s,
            'E' => $this->skor_e,
            'C' => $this->skor_c,
        ];
    }
}
