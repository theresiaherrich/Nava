<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class OrangTuaAuthController extends Controller
{
    public function showLogin()
    {
        if (session()->has('ortu_id_user')) {
            return redirect()->route('ortu.status');
        }

        return view('portal-orang-tua.auth.login');
    }

    public function showRegister()
    {
        if (session()->has('ortu_id_user')) {
            return redirect()->route('ortu.status');
        }

        return view('portal-orang-tua.auth.register');
    }

    /**
     * Registrasi akun orang tua.
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'nis' => ['required', 'string', 'max:30'],
            'nama' => ['required', 'string', 'max:120'],
            'hubungan' => [
                'required',
                Rule::in(['Ayah', 'Ibu', 'Wali'])
            ],
            'no_telepon' => ['required', 'string', 'max:25'],
            'username' => [
                'required',
                'string',
                'max:50',
                'regex:/^[A-Za-z0-9._-]+$/',
                'unique:user,username'
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $normalizePhone = function ($phone) {
            return preg_replace('/\D+/', '', $phone);
        };

        $userId = DB::transaction(function () use (
            $data,
            $normalizePhone
        ) {
            // 1. Cari siswa berdasarkan NIS.
            $siswa = DB::table('siswa')
                ->where('nis', $data['nis'])
                ->lockForUpdate()
                ->first();

            if (!$siswa) {
                throw ValidationException::withMessages([
                    'nis' => 'NIS tidak ditemukan dalam database sekolah.'
                ]);
            }

            // 2. Cocokkan identitas orang tua.
            $ortu = DB::table('orang_tua')
                ->where('id_siswa', $siswa->id_siswa)
                ->where('hubungan', $data['hubungan'])
                ->lockForUpdate()
                ->first();

            if (!$ortu) {
                throw ValidationException::withMessages([
                    'hubungan' => 'Data hubungan orang tua tidak ditemukan.'
                ]);
            }

            // 3. Cocokkan nama.
            if (
                mb_strtolower(trim($ortu->nama)) !==
                mb_strtolower(trim($data['nama']))
            ) {
                throw ValidationException::withMessages([
                    'nama' => 'Nama tidak sesuai dengan data orang tua di sekolah.'
                ]);
            }

            // 4. Periksa status relasi.
            if ($ortu->status_verifikasi === 'rejected') {
                throw ValidationException::withMessages([
                    'nis' => 'Pengajuan relasi sebelumnya ditolak. Hubungi Admin A4.'
                ]);
            }

            if ($ortu->id_user !== null) {
                throw ValidationException::withMessages([
                    'nis' => 'Relasi ini sudah memiliki akun. Silakan login.'
                ]);
            }

            // 5. Cocokkan nomor telepon jika sudah terdaftar.
            if (
                !empty($ortu->no_telepon) &&
                $normalizePhone($ortu->no_telepon) !==
                $normalizePhone($data['no_telepon'])
            ) {
                throw ValidationException::withMessages([
                    'no_telepon' => 'Nomor telepon tidak sesuai dengan data sekolah.'
                ]);
            }

            // 6. Buat akun pada tabel user.
            $idUser = DB::table('user')->insertGetId([
                'username' => $data['username'],
                'password' => Hash::make($data['password']),
                'must_change_password' => 0,
                'role' => 'ORANG_TUA',
            ]);

            // 7. Hubungkan akun dengan data orang tua.
            DB::table('orang_tua')
                ->where('id_siswa', $siswa->id_siswa)
                ->where('hubungan', $data['hubungan'])
                ->update([
                    'id_user' => $idUser,

                    // Nomor baru disimpan jika sebelumnya belum tersedia.
                    'no_telepon' => $ortu->no_telepon
                        ?: $data['no_telepon'],
                ]);

            return $idUser;
        });

        // 8. Login otomatis setelah registrasi.
        $request->session()->forget([
            'admin_id_user',
            'admin_username',
            'portal_siswa_id'
        ]);

        $request->session()->regenerate();

        $request->session()->put([
            'ortu_id_user' => $userId,
            'ortu_username' => $data['username'],
        ]);

        return redirect()->route('ortu.status')
            ->with('success', 'Registrasi berhasil. Silakan menunggu verifikasi Admin A4.');
    }

    /**
     * Login orang tua.
     */
    public function login(Request $request)
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string'],
        ]);

        $user = DB::table('user')
            ->where('username', $data['username'])
            ->where('role', 'ORANG_TUA')
            ->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'username' => 'Username atau kata sandi tidak sesuai.'
            ]);
        }

        $relations = DB::table('orang_tua')
            ->where('id_user', $user->id_user)
            ->get();

        if ($relations->isEmpty()) {
            throw ValidationException::withMessages([
                'username' => 'Akun belum terhubung dengan data orang tua.'
            ]);
        }

        // Pisahkan sesi Admin dan Orang Tua.
        $request->session()->forget([
            'admin_id_user',
            'admin_username',
            'portal_siswa_id'
        ]);

        $request->session()->regenerate();

        $request->session()->put([
            'ortu_id_user' => $user->id_user,
            'ortu_username' => $user->username,
        ]);

        // Ambil siswa yang relasinya sudah diverifikasi.
        $verified = $relations->firstWhere(
            'status_verifikasi',
            'verified'
        );

        if ($verified) {
            $request->session()->put(
                'portal_siswa_id',
                $verified->id_siswa
            );

            return redirect()->route('portal.dashboard');
        }

        return redirect()->route('ortu.status');
    }

    /**
     * Halaman status verifikasi.
     */
    public function status()
    {
        $userId = session('ortu_id_user');

        $relations = DB::table('orang_tua as ot')
            ->join('siswa as s', 's.id_siswa', '=', 'ot.id_siswa')
            ->where('ot.id_user', $userId)
            ->select(
                'ot.nama',
                'ot.hubungan',
                'ot.status_verifikasi',
                's.nama as nama_siswa',
                's.nis'
            )
            ->get();

        return view(
            'portal-orang-tua.auth.status',
            compact('relations')
        );
    }

    /**
     * Logout orang tua.
     */
    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('ortu.login')
            ->with('success', 'Berhasil keluar dari Portal Orang Tua.');
    }
}
