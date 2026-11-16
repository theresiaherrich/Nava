<?php

namespace App\Support;

use App\Exceptions\AturanBisnisException;
use App\Models\Guru;
use App\Models\OrangTua;
use App\Models\Semester;
use App\Models\Siswa;
use App\Models\User;
use App\Models\WaliKelas;
use Illuminate\Support\Facades\Auth;

/**
 * Konteks akses pengguna yang sedang login: peran aktif, profil, dan cakupan data.
 * Aturan per objek (rombel, kelas, siswa) ada di PenilaianAccess dan RaporAccess.
 */
class AcademicAccess
{
    public const SESSION_KEY = 'active_role';

    public static function user(): User
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            throw new AturanBisnisException('Silakan login terlebih dahulu.', 'UNAUTHENTICATED', 401);
        }

        return $user;
    }

    /** Peran aktif dari sesi; bila belum dipilih/tidak valid, pakai peran prioritas tertinggi. */
    public static function peranAktif(): ?string
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            return null;
        }

        $kode = $user->kodeRoles();
        $session = request()->hasSession() ? request()->session() : null;
        $aktif = $session?->get(self::SESSION_KEY);

        if (! in_array($aktif, $kode, true)) {
            $aktif = $kode[0] ?? null;
            $session?->put(self::SESSION_KEY, $aktif);
        }

        return $aktif;
    }

    public static function setPeranAktif(string $kode): void
    {
        request()->session()->put(self::SESSION_KEY, $kode);
    }

    public static function peranIs(string ...$kode): bool
    {
        return in_array(self::peranAktif(), $kode, true);
    }

    public static function guru(): Guru
    {
        return self::user()->guru ?? throw AturanBisnisException::dilarang('Akun Anda belum ditautkan ke data guru.', 'PROFILE_NOT_LINKED');
    }

    public static function siswa(): Siswa
    {
        return self::user()->siswa ?? throw AturanBisnisException::dilarang('Akun Anda belum ditautkan ke data siswa.', 'PROFILE_NOT_LINKED');
    }

    public static function orangTua(): OrangTua
    {
        return self::user()->orangTua ?? throw AturanBisnisException::dilarang('Akun Anda belum ditautkan ke data orang tua.', 'PROFILE_NOT_LINKED');
    }

    /** @return list<int> id_siswa anak yang tertaut ke akun orang tua. */
    public static function idAnak(): array
    {
        return self::orangTua()->anak()->pluck('siswa.id_siswa')->map(fn ($id) => (int) $id)->all();
    }

    public static function semesterAktif(): Semester
    {
        return Semester::aktif() ?? throw new AturanBisnisException('Belum ada semester aktif. Minta Kurikulum mengaktifkan semester.', 'NO_ACTIVE_SEMESTER');
    }

    /** Semester dari id (jika diisi) atau semester aktif. */
    public static function semester(?int $id = null): Semester
    {
        return $id ? Semester::findOrFail($id) : self::semesterAktif();
    }

    /** @return list<int> id_kelas yang diwalikan guru login pada semester tersebut. */
    public static function idKelasWali(int $idSemester): array
    {
        $guru = self::user()->guru;
        if (! $guru) {
            return [];
        }

        return WaliKelas::where('id_guru', $guru->id_guru)->where('id_semester', $idSemester)
            ->pluck('id_kelas')->map(fn ($id) => (int) $id)->all();
    }
}
