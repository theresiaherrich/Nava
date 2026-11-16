<?php

namespace App\Services;

use App\Models\Semester;
use App\Models\TahunAjaran;
use Illuminate\Support\Facades\DB;

/** Tahun ajaran selalu punya semester GANJIL & GENAP; hanya satu TA dan satu semester aktif. */
class TahunAjaranService
{
    public function __construct(private AuditService $audit) {}

    public function buat(string $nama): TahunAjaran
    {
        return DB::transaction(function () use ($nama) {
            $ta = TahunAjaran::create(['nama' => $nama, 'aktif' => false]);
            foreach (['GANJIL', 'GENAP'] as $s) {
                Semester::create(['id_tahun_ajaran' => $ta->id_tahun_ajaran, 'nama' => $s, 'aktif' => false]);
            }
            $this->audit->catat('buat_tahun_ajaran', 'tahun_ajaran', $ta->id_tahun_ajaran, null, ['nama' => $nama]);

            return $ta->load('semester');
        });
    }

    public function aktifkanSemester(Semester $semester): Semester
    {
        return DB::transaction(function () use ($semester) {
            $sebelum = Semester::where('aktif', true)->value('id_semester');

            Semester::where('aktif', true)->lockForUpdate()->update(['aktif' => false]);
            TahunAjaran::where('aktif', true)->update(['aktif' => false]);
            $semester->update(['aktif' => true]);
            $semester->tahunAjaran()->update(['aktif' => true]);

            $this->audit->catat('aktifkan_semester', 'semester', $semester->id_semester, ['id_semester_aktif' => $sebelum], ['id_semester_aktif' => $semester->id_semester]);

            return $semester->fresh('tahunAjaran');
        });
    }
}
