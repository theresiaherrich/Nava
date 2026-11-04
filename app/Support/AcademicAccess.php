<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class AcademicAccess
{
    public static function role(): string
    {
        abort_unless(auth()->check(), 401, 'Login diperlukan.');
        return strtoupper((string) auth()->user()->role);
    }

    public static function manage(): void
    {
        abort_unless(in_array(self::role(), ['ADMIN', 'KURIKULUM'], true), 403, 'Hanya admin/kurikulum.');
    }

    public static function teacherId(): ?int
    {
        $id = DB::table('guru')->where('id_user', auth()->id())->value('id_guru');
        return $id === null ? null : (int) $id;
    }

    public static function canTeach(int $rombelId): void
    {
        if (in_array(self::role(), ['ADMIN', 'KURIKULUM'], true)) return;
        $id = self::teacherId();
        abort_unless($id !== null && DB::table('guru_rombel')->where('id_guru', $id)->where('id_rombel', $rombelId)->exists(), 403, 'Bukan guru pengampu rombel ini.');
    }

    public static function canReadRombel(int $rombelId): void
    {
        $role = self::role();
        if (in_array($role, ['ADMIN', 'KURIKULUM'], true)) return;
        self::canTeach($rombelId);
    }

    public static function editable(int $rombelId): void
    {
        $rombel = DB::table('rombel')->where('id_rombel', $rombelId)->first();
        abort_unless($rombel !== null, 404, 'Rombel tidak ditemukan.');
        $blocked = DB::table('periode_nilai')->where('id_semester', $rombel->id_semester)
            ->where('tingkat', $rombel->tingkat)
            ->whereIn('status', ['ditutup', 'terkunci'])->exists();
        abort_if($blocked, 409, 'Periode nilai sudah ditutup/terkunci.');
        abort_if(DB::table('paket_nilai')->where('id_rombel', $rombelId)
            ->whereIn('status', ['terkirim', 'disetujui', 'terkunci'])->exists(), 409, 'Paket nilai tidak dapat diubah.');
    }
}
