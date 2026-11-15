<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Semester;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Validation\ValidationException;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /** Semester dari query ?id_semester=, atau semester yang sedang aktif. */
    protected function semesterDari(Request $request): Semester
    {
        $id = $request->integer('id_semester') ?: Semester::where('aktif', true)->value('id_semester');
        $semester = $id ? Semester::find($id) : null;

        if (! $semester) {
            throw ValidationException::withMessages([
                'id_semester' => 'Semester tidak ditemukan. Aktifkan semester terlebih dahulu.',
            ]);
        }

        return $semester;
    }

    protected function pastikanSatuTahunAjaran(Kelas $kelas, Semester $semester): void
    {
        if ((int) $kelas->id_tahun_ajaran !== (int) $semester->id_tahun_ajaran) {
            throw ValidationException::withMessages([
                'id_semester' => 'Kelas dan semester berasal dari tahun ajaran yang berbeda.',
            ]);
        }
    }
}
