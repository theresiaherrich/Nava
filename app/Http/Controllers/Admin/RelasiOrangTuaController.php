<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class RelasiOrangTuaController extends Controller
{
    private function actorId(): int
    {
        return (int) session('admin_id_user');
    }

public function index(Request $request)
{
    $query = DB::table('orang_tua as ot')
        ->join('siswa as s', 's.id_siswa', '=', 'ot.id_siswa')
        ->leftJoin('user as u', 'u.id_user', '=', 'ot.id_user')
        ->leftJoin('riwayat_kelas as rk', 'rk.id_siswa', '=', 's.id_siswa')
        ->leftJoin('semester as sem', 'sem.id_semester', '=', 'rk.id_semester')
        ->leftJoin('kelas as k', 'k.id_kelas', '=', 'rk.id_kelas')
        ->where(function ($q) {
            $q->whereNull('sem.aktif')
              ->orWhere('sem.aktif', 1);
        })
        ->select(
            'ot.id_siswa',
            'ot.id_user',
            'ot.hubungan',
            'ot.nama as nama_orang_tua',
            'ot.no_telepon',
            'ot.status_verifikasi',

            'u.username as username_akun',

            's.nama as nama_siswa',
            's.nis',
            'k.nama as nama_kelas'
        );

    // Pencarian nama orang tua, siswa, atau NIS
    if ($request->filled('q')) {
        $term = '%' . $request->input('q') . '%';

        $query->where(function ($q) use ($term) {
            $q->where('ot.nama', 'like', $term)
              ->orWhere('s.nama', 'like', $term)
              ->orWhere('s.nis', 'like', $term)
              ->orWhere('u.username', 'like', $term);
        });
    }

    // Filter tingkat kelas
    if ($request->filled('tingkat')) {
        $query->where('k.tingkat', $request->input('tingkat'));
    }

    // Filter status verifikasi
    if ($request->filled('status')) {
        $query->where(
            'ot.status_verifikasi',
            $request->input('status')
        );
    }

    // Filter sumber relasi berdasarkan kepemilikan akun
    if ($request->input('akun') === 'terhubung') {
        $query->whereNotNull('ot.id_user');
    } elseif ($request->input('akun') === 'belum') {
        $query->whereNull('ot.id_user');
    }

    $verifiedCount = DB::table('orang_tua')
        ->whereNotNull('id_user')
        ->where('status_verifikasi', 'verified')
        ->count();

    $pendingCount = DB::table('orang_tua')
        ->whereNotNull('id_user')
        ->where('status_verifikasi', 'pending')
        ->count();

    $relations = $query
        ->orderByRaw("
            CASE
                WHEN ot.id_user IS NOT NULL
                AND ot.status_verifikasi = 'pending' THEN 0
                ELSE 1
            END
        ")
        ->orderBy('ot.nama')
        ->paginate(10)
        ->withQueryString();

    $students = DB::table('siswa')
        ->orderBy('nama')
        ->get(['id_siswa', 'nama', 'nis']);

    return view('admin.relations.index', compact(
        'relations',
        'students',
        'verifiedCount',
        'pendingCount'
    ));
}

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_siswa' => ['required', 'integer', 'exists:siswa,id_siswa'],
            'hubungan' => ['required', Rule::in(['Ayah', 'Ibu', 'Wali'])],
            'nama' => ['required', 'string', 'max:120'],
            'no_telepon' => ['nullable', 'string', 'max:25'],
            'jenis_kelamin' => ['nullable', Rule::in(['L', 'P'])],
            'alamat' => ['nullable', 'string'],
        ]);

        $exists = DB::table('orang_tua')
            ->where('id_siswa', $data['id_siswa'])
            ->where('hubungan', $data['hubungan'])
            ->exists();

        if ($exists) {
            return back()
                ->withErrors(['hubungan' => 'Relasi dengan jenis hubungan tersebut sudah ada untuk siswa ini.'])
                ->withInput();
        }

        DB::table('orang_tua')->insert($data + [
            'status_verifikasi' => 'pending',
        ]);

        return back()->with(
            'success',
            'Relasi berhasil ditambahkan dengan status menunggu verifikasi.'
        );
    }


public function verify(Request $request, int $idSiswa, string $hubungan)
{
    $data = $request->validate([
        'status' => [
            'required',
            Rule::in(['verified', 'rejected']),
        ],
    ]);

    DB::transaction(function () use ($idSiswa, $hubungan, $data) {

        $relation = DB::table('orang_tua')
            ->where('id_siswa', $idSiswa)
            ->where('hubungan', $hubungan)
            ->lockForUpdate()
            ->first();

        if (!$relation) {
            abort(404, 'Relasi orang tua tidak ditemukan.');
        }

        // Hanya relasi yang memiliki akun yang boleh diverifikasi
        if (is_null($relation->id_user)) {
            abort(
                403,
                'Relasi ini belum terhubung dengan akun Orang Tua.'
            );
        }

        DB::table('orang_tua')
            ->where('id_siswa', $idSiswa)
            ->where('hubungan', $hubungan)
            ->update([
                'status_verifikasi' => $data['status'],
                'diverifikasi_oleh' => $this->actorId(),
                'waktu_verifikasi' => now(),
            ]);

        $this->log(
            'ubah_status_relasi',
            'orang_tua',
            $idSiswa,
            [
                'hubungan' => $hubungan,
                'id_user' => $relation->id_user,
                'nama_orang_tua' => $relation->nama,
                'status_sebelum' => $relation->status_verifikasi,
                'status_verifikasi' => $data['status'],
            ]
        );
    });

    return back()->with(
        'success',
        'Status verifikasi relasi orang tua berhasil diperbarui.'
    );
}

    public function destroy(int $idSiswa, string $hubungan)
    {
        $relation = DB::table('orang_tua')
            ->where('id_siswa', $idSiswa)
            ->where('hubungan', $hubungan)
            ->first();

        if (! $relation) {
            return back()->with('error', 'Relasi orang tua tidak ditemukan.');
        }

        DB::transaction(function () use ($idSiswa, $hubungan, $relation) {
            DB::table('orang_tua')
                ->where('id_siswa', $idSiswa)
                ->where('hubungan', $hubungan)
                ->delete();

            $this->log(
                'hapus_relasi',
                'orang_tua',
                $idSiswa,
                [
                    'hubungan' => $hubungan,
                    'nama' => $relation->nama,
                ]
            );
        });

        return back()->with('success', 'Relasi orang tua dan siswa dihapus.');
    }

    private function log(string $aksi, string $entity, int $entityId, array $after = []): void
    {
        if (! Schema::hasTable('log_aktivitas')) {
            return;
        }

        DB::table('log_aktivitas')->insert([
            'id_user' => $this->actorId(),
            'aksi' => $aksi,
            'nama_entitas' => $entity,
            'entitas_id' => $entityId,
            'data_sesudah' => json_encode($after),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
