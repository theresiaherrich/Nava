<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Pengajuan bimbingan konseling siswa.
 * Tabel: pengajuan_bk. PK: id_pengajuan.
 *
 * Perubahan dari DB lama:
 * - PK: id_pengajuan_bk → id_pengajuan
 * - Relasi: id_riwayat_kelas → id_siswa + id_semester
 * - balasan_bk → balasan
 * - jadwal_bimbingan (DATETIME) → tanggal_bimbingan (DATE) + jam_bimbingan (TIME)
 */
class PengajuanBk extends Model
{
    protected $table = 'pengajuan_bk';

    protected $primaryKey = 'id_pengajuan';

    public $timestamps = true;

    protected $dateFormat = 'Y-m-d H:i:s';

    protected $fillable = [
        'id_siswa',
        'id_semester',
        'topik',
        'deskripsi',
        'status',               // menunggu | diproses | dijadwalkan | selesai | ditolak
        'id_guru',
        'balasan',              // dulu balasan_bk
        'tanggal_bimbingan',   // dulu bagian dari jadwal_bimbingan
        'jam_bimbingan',       // dulu bagian dari jadwal_bimbingan
    ];

    protected $casts = [
        'tanggal_bimbingan' => 'date',
        'created_at'        => 'datetime',
        'updated_at'        => 'datetime',
    ];

    /** Daftar pilihan topik yang valid. */
    public const TOPIK_LIST = [
        'Konsultasi pilihan jurusan',
        'Motivasi belajar menurun',
        'Perencanaan studi lanjut',
        'Kendala pertemanan',
        'Persiapan masuk PTN',
        'Perubahan pilihan jurusan',
        'Adaptasi belajar',
    ];

    // ─── Relasi ─────────────────────────────────────────────────────────────

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'id_siswa', 'id_siswa');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'id_semester', 'id_semester');
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class, 'id_guru', 'id_guru');
    }
}
