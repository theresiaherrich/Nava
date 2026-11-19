<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class ManajemenPenggunaController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('orang_tua as ot')
            ->leftJoin('user as u', 'u.id_user', '=', 'ot.id_user')
            ->selectRaw(
                'ot.id_user, MAX(u.username) as username, MAX(u.role) as role, COUNT(DISTINCT ot.id_siswa) as jumlah_anak, GROUP_CONCAT(DISTINCT ot.nama ORDER BY ot.nama SEPARATOR ", ") as nama_orang_tua'
            )
            ->whereNotNull('ot.id_user')
            ->groupBy('ot.id_user');

        if ($request->filled('q')) {
            $term = '%' . (string) $request->input('q') . '%';
            $query->havingRaw(
                '(MAX(u.username) LIKE ? OR GROUP_CONCAT(ot.nama SEPARATOR " ") LIKE ?)',
                [$term, $term]
            );
        }

        $users = $query->orderBy('username')
            ->paginate(8)
            ->withQueryString();

        $unlinked = DB::table('orang_tua')
            ->whereNull('id_user')
            ->select('id_siswa', 'hubungan', 'nama')
            ->orderBy('nama')
            ->get();

        return view('admin.users.index', compact('users', 'unlinked'));
    }

    public function export()
    {
        $rows = DB::table('orang_tua as ot')
            ->join('user as u', 'u.id_user', '=', 'ot.id_user')
            ->where('u.role', 'ORANG_TUA')
            ->select(
                'u.username',
                'ot.nama',
                'ot.hubungan',
                'ot.no_telepon',
                'ot.status_verifikasi'
            )
            ->orderBy('ot.nama')
            ->get();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'Username',
                'Nama Orang Tua',
                'Hubungan',
                'Nomor Telepon',
                'Status Verifikasi',
            ]);

            foreach ($rows as $row) {
                fputcsv($out, [
                    $row->username,
                    $row->nama,
                    $row->hubungan,
                    $row->no_telepon,
                    $row->status_verifikasi,
                ]);
            }

            fclose($out);
        }, 'data-akun-orang-tua.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'parent_key' => ['required', 'string'],
            'username' => ['required', 'string', 'max:50', 'unique:user,username'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        [$parentId, $parentRelation] = array_pad(
            explode('|', $data['parent_key'], 2),
            2,
            ''
        );

        if (! ctype_digit($parentId) || ! in_array($parentRelation, ['Ayah', 'Ibu', 'Wali'], true)) {
            return back()
                ->withErrors(['parent_key' => 'Pilihan orang tua tidak valid.'])
                ->withInput();
        }

        $parent = DB::table('orang_tua')
            ->where('id_siswa', (int) $parentId)
            ->where('hubungan', $parentRelation)
            ->first();

        if (! $parent || $parent->id_user) {
            return back()
                ->withErrors(['parent_key' => 'Data orang tua tidak ditemukan atau sudah memiliki akun.'])
                ->withInput();
        }

        DB::transaction(function () use ($data, $parentId, $parentRelation) {
            $id = DB::table('user')->insertGetId([
                'username' => $data['username'],
                'password' => Hash::make($data['password']),
                'must_change_password' => 1,
                'role' => 'ORANG_TUA',
            ]);

            DB::table('orang_tua')
                ->where('id_siswa', (int) $parentId)
                ->where('hubungan', $parentRelation)
                ->update(['id_user' => $id]);
        });

        return back()->with('success', 'Akun orang tua berhasil dibuat dan dihubungkan.');
    }

    public function updateUsername(Request $request, int $id)
    {
        $data = $request->validate([
            'username' => [
                'required',
                'string',
                'max:50',
                Rule::unique('user', 'username')->ignore($id, 'id_user'),
            ],
        ]);

        $updated = DB::table('user')
            ->where('id_user', $id)
            ->where('role', 'ORANG_TUA')
            ->update(['username' => $data['username']]);

        return back()->with(
            $updated ? 'success' : 'error',
            $updated
                ? 'Username berhasil diperbarui.'
                : 'Akun tidak ditemukan atau tidak ada perubahan.'
        );
    }

    public function resetPassword(Request $request, int $id)
    {
        $data = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $updated = DB::table('user')
            ->where('id_user', $id)
            ->where('role', 'ORANG_TUA')
            ->update([
                'password' => Hash::make($data['password']),
                'must_change_password' => 1,
            ]);

        return back()->with(
            $updated ? 'success' : 'error',
            $updated
                ? 'Kata sandi direset. Orang tua diminta menggantinya saat masuk.'
                : 'Akun tidak ditemukan.'
        );
    }
}
