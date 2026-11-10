<?php

namespace App\Http\Controllers\A3_BK\Concerns;

use App\Models\Guru;
use App\Models\Semester;
use Illuminate\Support\Facades\Auth;

/**
 * Konteks Guru BK yang sedang login (dipakai semua controller area Guru BK).
 */
trait KonteksGuruBk
{
    /**
     * @return array{user: \App\Models\User, guru: \App\Models\Guru, guruBk: \App\Models\GuruBk, semester: \App\Models\Semester}
     */
    protected function getKonteksGuru(): array
    {
        $user = Auth::user();
        $guru = Guru::where('id_user', $user->id_user)->with('guruBk')->firstOrFail();
        $guruBk = $guru->guruBk;

        $semester = Semester::where('aktif', true)
            ->with('tahunAjaran')
            ->orderByDesc('id_semester')
            ->first();

        if (! $semester) {
            abort(503, 'Tidak ada semester aktif saat ini.');
        }

        return compact('user', 'guru', 'guruBk', 'semester');
    }
}
