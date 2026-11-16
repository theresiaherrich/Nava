<?php

namespace App\Services;

use App\Exceptions\AturanBisnisException;
use App\Models\PaketNilai;
use App\Models\Rombel;
use App\Models\WaliKelas;
use App\Support\AcademicAccess;

/**
 * Satu tempat aturan akses data penilaian, supaya tidak ada endpoint yang lupa memeriksa.
 * Guru hanya rombel yang ia ampu; wali hanya kelasnya; Kurikulum semua.
 */
class PenilaianAccess
{
    public static function pastikanPengampu(Rombel $rombel): void
    {
        $guru = AcademicAccess::guru();
        $mengampu = $rombel->guru()->where('guru.id_guru', $guru->id_guru)->exists();

        if (! AcademicAccess::peranIs('GURU') || ! $mengampu) {
            throw AturanBisnisException::dilarang('Rombel ini bukan rombel yang Anda ampu.', 'NOT_PENGAMPU');
        }
    }

    /** Boleh membaca rekap rombel: Kurikulum, pengampu, atau wali dari kelas peserta. */
    public static function pastikanBolehBacaRombel(Rombel $rombel): void
    {
        if (AcademicAccess::peranIs('KURIKULUM')) {
            return;
        }
        if (AcademicAccess::peranIs('GURU')) {
            self::pastikanPengampu($rombel);

            return;
        }

        throw AturanBisnisException::dilarang('Anda tidak memiliki akses ke rombel ini.');
    }

    public static function pastikanWaliPaket(PaketNilai $paket): void
    {
        self::pastikanWaliKelas($paket->id_kelas, $paket->rombel->id_semester);
    }

    public static function pastikanWaliKelas(int $idKelas, int $idSemester): void
    {
        $guru = AcademicAccess::user()->guru;
        $wali = $guru && WaliKelas::where('id_kelas', $idKelas)->where('id_semester', $idSemester)
            ->where('id_guru', $guru->id_guru)->exists();

        if (! AcademicAccess::peranIs('WALI_KELAS') || ! $wali) {
            throw AturanBisnisException::dilarang('Anda bukan wali kelas dari kelas ini.', 'NOT_WALI_KELAS');
        }
    }
}
