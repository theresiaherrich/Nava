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
            ->leftJoin('riwayat_kelas as rk', 'rk.id_siswa', '=', 's.id_siswa')
            ->leftJoin('semester as sem', 'sem.id_semester', '=', 'rk.id_semester')
            ->leftJoin('kelas as k', 'k.id_kelas', '=', 'rk.id_kelas')
            ->where(function ($q) {
                $q->whereNull('sem.aktif')->orWhere('sem.aktif', 1);
            })
            ->select(
                'ot.id_siswa',
                'ot.hubungan',
                'ot.nama as nama_orang_tua',
                'ot.no_telepon',
                'ot.status_verifikasi',
                's.nama as nama_siswa',
                's.nis',
                'k.nama as nama_kelas'
            );

        if ($request->filled('q')) {
            $term = '%' . (string) $request->input('q') . '%';
            $query->where(function ($q) use ($term) {
                $q->where('ot.nama', 'like', $term)
                    ->orWhere('s.nama', 'like', $term)
                    ->orWhere('s.nis', 'like', $term);
            });
        }

        if ($request->filled('tingkat')) {
            $query->where('k.tingkat', (string) $request->input('tingkat'));
        }

        if ($request->filled('status')) {
            $query->where('ot.status_verifikasi', (string) $request->input('status'));
        }

        $verifiedCount = DB::table('orang_tua')
            ->where('status_verifikasi', 'verified')
            ->count();

        $relations = $query
            ->orderBy('ot.nama')
            ->paginate(10)
            ->withQueryString();

        $students = DB::table('siswa')
            ->orderBy('nama')
            ->get(['id_siswa', 'nama', 'nis']);

        return view('admin.relations.index', compact(
            'relations',
            'students',
            'verifiedCount'
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
            'status' => ['required', Rule::in(['verified', 'rejected', 'pending'])],
        ]);

        DB::transaction(function () use ($idSiswa, $hubungan, $data) {
            $before = DB::table('orang_tua')
                ->where('id_siswa', $idSiswa)
                ->where('hubungan', $hubungan)
                ->first();

            if (! $before) {
                abort(404, 'Relasi orang tua tidak ditemukan.');
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
                    'status_sebelum' => $before->status_verifikasi,
                    'status_verifikasi' => $data['status'],
                ]
            );
        });

        return back()->with('success', 'Status verifikasi relasi diperbarui.');
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
