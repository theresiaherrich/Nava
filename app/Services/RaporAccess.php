<?php

namespace App\Services;

use App\Exceptions\AturanBisnisException;
use App\Models\Kelas;
use App\Models\RiwayatKelas;
use App\Models\Siswa;
use App\Models\SiswaRombel;
use App\Support\AcademicAccess;

/**
 * Akses data per siswa/kelas:
 *  SISWA     → dirinya sendiri;
 *  ORANG_TUA → anak yang tertaut;
 *  WALI_KELAS→ siswa di kelas yang ia walikan (semester terkait);
 *  GURU      → siswa yang menjadi peserta rombel yang ia ampu;
 *  KURIKULUM → semua.
 * Pelanggaran → 403.
 */
class RaporAccess
{
    public static function pastikanBolehLihatSiswa(Siswa $siswa, ?int $idSemester = null): void
    {
        $peran = AcademicAccess::peranAktif();

        $boleh = match ($peran) {
            'KURIKULUM' => true,
            'SISWA' => (int) AcademicAccess::siswa()->id_siswa === (int) $siswa->id_siswa,
            'ORANG_TUA' => in_array((int) $siswa->id_siswa, AcademicAccess::idAnak(), true),
            'WALI_KELAS' => self::siswaDiKelasWali($siswa, $idSemester),
            'GURU' => self::siswaDiRombelGuru($siswa),
            default => false,
        };

        if (! $boleh) {
            throw AturanBisnisException::dilarang('Anda tidak memiliki akses ke data siswa ini.', 'STUDENT_FORBIDDEN');
        }
    }

    /** Wali kelas hanya kelasnya; Kurikulum semua. */
    public static function pastikanKelolaKelas(Kelas $kelas, int $idSemester): void
    {
        if (AcademicAccess::peranIs('KURIKULUM')) {
            return;
        }
        PenilaianAccess::pastikanWaliKelas($kelas->id_kelas, $idSemester);
    }

    /** Siswa milik akun (SISWA) atau anak pilihan (ORANG_TUA, ?id_siswa=). */
    public static function siswaPortal(?int $idSiswa): Siswa
    {
        if (AcademicAccess::peranIs('SISWA')) {
            return AcademicAccess::siswa();
        }

        $anak = AcademicAccess::idAnak();
        if ($anak === []) {
            throw AturanBisnisException::dilarang('Belum ada anak yang tertaut ke akun Anda.', 'NO_CHILD_LINKED');
        }
        $id = $idSiswa ?? (count($anak) === 1 ? $anak[0] : null);
        if ($id === null) {
            throw new AturanBisnisException('Pilih anak terlebih dahulu (parameter id_siswa).', 'CHILD_REQUIRED', 422, ['id_siswa' => ['Wajib diisi bila anak lebih dari satu.']]);
        }
        if (! in_array($id, $anak, true)) {
            throw AturanBisnisException::dilarang('Siswa ini bukan anak yang tertaut ke akun Anda.', 'STUDENT_FORBIDDEN');
        }

        return Siswa::findOrFail($id);
    }

    private static function siswaDiKelasWali(Siswa $siswa, ?int $idSemester): bool
    {
        $semester = $idSemester ?? AcademicAccess::semesterAktif()->id_semester;
        $kelas = AcademicAccess::idKelasWali($semester);

        return $kelas !== [] && RiwayatKelas::where('id_siswa', $siswa->id_siswa)->where('id_semester', $semester)->whereIn('id_kelas', $kelas)->exists();
    }

    private static function siswaDiRombelGuru(Siswa $siswa): bool
    {
        $guru = AcademicAccess::user()->guru;

        return $guru !== null && SiswaRombel::whereHas('riwayatKelas', fn ($q) => $q->where('id_siswa', $siswa->id_siswa))
            ->whereHas('rombel.guru', fn ($q) => $q->where('guru.id_guru', $guru->id_guru))->exists();
    }
}
