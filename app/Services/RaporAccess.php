<?php

namespace App\Services;

use App\Models\User;
use App\Models\WaliKelas;
use Illuminate\Support\Collection;

/**
 * Aturan akses bagian 4.7 dan 4.8. Memakai PenilaianAccess untuk pengecekan wali kelas
 * (tanpa mengubahnya), karena middleware role saja tidak cukup: wali hanya boleh kelasnya.
 */
class RaporAccess
{
    /** Admin/kurikulum melihat semua kelas; wali kelas hanya kelas yang ia pegang. */
    public static function bisaMelihatKelas(User $user, int $kelasId): bool
    {
        return $user->hasAnyRole(['admin', 'kurikulum']) || PenilaianAccess::bisaMemeriksaKelas($user, $kelasId);
    }

    /** Hanya wali kelas dari kelas tersebut (untuk menulis catatan wali). */
    public static function bisaMengisiKelas(User $user, int $kelasId): bool
    {
        return PenilaianAccess::bisaMemeriksaKelas($user, $kelasId);
    }

    public static function istimewa(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'kurikulum']);
    }

    /** @return Collection<int, int> ID kelas yang dipegang user sebagai wali kelas. */
    public static function kelasIdsWali(User $user): Collection
    {
        $guruId = PenilaianAccess::guruId($user);

        return $guruId !== null && $user->hasAnyRole(['wali_kelas'])
            ? WaliKelas::where('guru_id', $guruId)->pluck('kelas_id')
            : collect();
    }
}
