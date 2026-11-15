<?php

namespace App\Services;

use App\Models\PaketNilai;
use App\Models\PeriodeNilai;
use App\Models\Rombel;
use App\Models\Tp;
use App\Models\User;
use App\Models\WaliKelas;

/**
 * Satu tempat untuk aturan akses dan aturan nilai. Controller memanggil ini,
 * bukan mengulang pengecekan sendiri, supaya tidak ada celah di satu endpoint.
 */
class PenilaianAccess
{
    public const MAKS_TP = 10;

    public static function guruId(User $user): ?int
    {
        return $user->guru?->id;
    }

    /** Admin/Kurikulum boleh mengelola TP; guru hanya rombel yang ia ampu. */
    public static function bisaKelolaRombel(User $user, Rombel $rombel): bool
    {
        if ($user->hasAnyRole(['admin', 'kurikulum'])) {
            return true;
        }

        return $user->hasAnyRole(['guru']) && $rombel->dapatDiampuOleh(self::guruId($user));
    }

    /** Hanya guru pengampu yang mengisi nilai (admin/kurikulum tidak mengisi nilai). */
    public static function bisaInputNilai(User $user, Rombel $rombel): bool
    {
        return $user->hasAnyRole(['guru']) && $rombel->dapatDiampuOleh(self::guruId($user));
    }

    public static function bisaMemeriksaKelas(User $user, int $kelasId): bool
    {
        $guruId = self::guruId($user);

        return $guruId !== null
            && $user->hasAnyRole(['wali_kelas'])
            && WaliKelas::where('kelas_id', $kelasId)->where('guru_id', $guruId)->exists();
    }

    /** Aturan dasar: TP STS = masuk_sts; SAS = TP bertanda is_sas. ASUMSI, perlu disepakati sekolah. */
    public static function tpDapatDiisi(PeriodeNilai $periode, Tp $tp): bool
    {
        return $periode->jenis === 'STS' ? $tp->masuk_sts : $tp->is_sas;
    }

    /** TP tidak boleh diubah/dihapus ketika nilai sedang dikirim, disetujui, atau sudah dikunci. */
    public static function pastikanRombelBisaDiubah(Rombel $rombel): void
    {
        $terkunci = PaketNilai::where('rombel_id', $rombel->id)
            ->whereIn('status', ['dikirim', 'disetujui', 'dikunci'])
            ->exists();

        abort_if($terkunci, 422, 'Ada paket nilai yang sedang dikirim atau dikunci. Minta Kurikulum membuka koreksi terlebih dahulu.');
    }
}
