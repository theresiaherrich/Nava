<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Pengajuan bimbingan konseling siswa.
 * Tabel: pengajuan_bk. PK: id_pengajuan_bk.
 * Kolom created_at / updated_at ada (tipe DATETIME, bukan TIMESTAMP).
 */
class PengajuanBk extends Model
{
    protected $table = 'pengajuan_bk';

    protected $primaryKey = 'id_pengajuan_bk';

    public $timestamps = true;         // ada created_at & updated_at

    // Kolom DATETIME di DB, bukan TIMESTAMP — perlu dateFormat manual
    protected $dateFormat = 'Y-m-d H:i:s';

    protected $fillable = [
        'id_riwayat_kelas', 'topik', 'deskripsi', 'status',
        'id_guru', 'balasan_bk', 'jadwal_bimbingan',
    ];

    protected $casts = [
        'jadwal_bimbingan' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /** Daftar pilihan topik yang valid (sesuai aturan bisnis). */
    public const TOPIK_LIST = [
        'Konsultasi pilihan jurusan',
        'Motivasi belajar menurun',
        'Perencanaan studi lanjut',
        'Kendala pertemanan',
        'Persiapan masuk PTN',
        'Perubahan pilihan jurusan',
        'Adaptasi belajar',
    ];

    public function riwayatKelas()
    {
        return $this->belongsTo(RiwayatKelas::class, 'id_riwayat_kelas', 'id_riwayat_kelas');
    }

    public function guruBk()
    {
        return $this->belongsTo(GuruBk::class, 'id_guru', 'id_guru');
    }
}
