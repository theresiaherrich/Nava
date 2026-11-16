<?php

namespace App\Services;

use App\Exceptions\AturanBisnisException;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Semester;
use App\Models\WaliKelas;
use Illuminate\Support\Facades\DB;

/** Penetapan wali kelas; peran WALI_KELAS diberikan/dicabut otomatis. */
class WaliKelasService
{
    public function __construct(private AuditService $audit, private AccountService $akun) {}

    public function tetapkan(Kelas $kelas, Guru $guru, Semester $semester): WaliKelas
    {
        if ((int) $kelas->id_tahun_ajaran !== (int) $semester->id_tahun_ajaran) {
            throw new AturanBisnisException('Kelas dan semester berasal dari tahun ajaran berbeda.');
        }
        if (! $guru->id_user) {
            throw new AturanBisnisException('Guru belum memiliki akun. Buat akun guru terlebih dahulu.', 'PROFILE_NOT_LINKED');
        }

        return DB::transaction(function () use ($kelas, $guru, $semester) {
            $lama = WaliKelas::where('id_kelas', $kelas->id_kelas)->where('id_semester', $semester->id_semester)->lockForUpdate()->first();
            $guruLama = $lama?->guru;

            $wali = WaliKelas::updateOrCreate(
                ['id_kelas' => $kelas->id_kelas, 'id_semester' => $semester->id_semester],
                ['id_guru' => $guru->id_guru],
            );

            $this->akun->sinkronPeranWali($guru);
            if ($guruLama && (int) $guruLama->id_guru !== (int) $guru->id_guru) {
                $this->akun->sinkronPeranWali($guruLama);
            }

            $this->audit->catat('tetapkan_wali_kelas', 'wali_kelas', $wali->id_wali_kelas,
                $lama ? ['id_guru' => $lama->id_guru] : null, ['id_guru' => $guru->id_guru, 'id_kelas' => $kelas->id_kelas, 'id_semester' => $semester->id_semester]);

            return $wali->load(['guru:id_guru,nama,nip', 'kelas:id_kelas,nama,tingkat']);
        });
    }

    public function hapus(WaliKelas $wali): void
    {
        DB::transaction(function () use ($wali) {
            $guru = $wali->guru;
            $wali->delete();
            $this->akun->sinkronPeranWali($guru);
            $this->audit->catat('hapus_wali_kelas', 'wali_kelas', $wali->id_wali_kelas, $wali->only(['id_kelas', 'id_guru', 'id_semester']));
        });
    }
}
