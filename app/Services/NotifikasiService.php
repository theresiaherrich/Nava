<?php

namespace App\Services;

use App\Models\Guru;
use App\Models\Notifikasi;
use App\Models\Rombel;
use App\Models\User;
use App\Models\WaliKelas;
use Illuminate\Support\Facades\DB;

/**
 * Notifikasi in-app untuk dropdown di semua peran.
 * Dipicu: nilai dikirim, diminta revisi, disetujui, periode dibuka/ditutup, rapor terbit, koreksi dibuka.
 */
class NotifikasiService
{
    /** @param iterable<int>|int $idUser */
    public function kirim(iterable|int $idUser, string $jenis, string $judul, string $pesan, array $data = []): int
    {
        $ids = collect(is_int($idUser) ? [$idUser] : $idUser)->filter()->unique()->values();
        if ($ids->isEmpty()) {
            return 0;
        }

        $now = now();
        Notifikasi::insert($ids->map(fn ($id) => [
            'id_user' => $id, 'jenis' => $jenis, 'judul' => $judul, 'pesan' => $pesan,
            'data' => json_encode($data), 'created_at' => $now, 'updated_at' => $now,
        ])->all());

        return $ids->count();
    }

    /** @return list<int> id_user guru pengampu rombel. */
    public function userGuruRombel(Rombel $rombel): array
    {
        return $rombel->guru()->whereNotNull('id_user')->pluck('guru.id_user')->map(fn ($id) => (int) $id)->all();
    }

    /** @return list<int> id_user wali kelas pada semester tersebut. */
    public function userWaliKelas(int $idKelas, int $idSemester): array
    {
        return WaliKelas::where('id_kelas', $idKelas)->where('id_semester', $idSemester)
            ->join('guru', 'guru.id_guru', '=', 'wali_kelas.id_guru')->whereNotNull('guru.id_user')
            ->pluck('guru.id_user')->map(fn ($id) => (int) $id)->all();
    }

    /** @return list<int> id_user semua guru pengampu rombel pada semester. */
    public function userGuruSemester(int $idSemester): array
    {
        return DB::table('guru_rombel')->join('rombel', 'rombel.id_rombel', '=', 'guru_rombel.id_rombel')
            ->join('guru', 'guru.id_guru', '=', 'guru_rombel.id_guru')
            ->where('rombel.id_semester', $idSemester)->whereNotNull('guru.id_user')
            ->distinct()->pluck('guru.id_user')->map(fn ($id) => (int) $id)->all();
    }

    /** @return list<int> */
    public function userPeran(string $kode): array
    {
        return User::where('status_akun', 'aktif')->whereHas('roles', fn ($q) => $q->where('kode', $kode))
            ->pluck('id_user')->map(fn ($id) => (int) $id)->all();
    }
}
