<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    /**
     * Tampilkan halaman login.
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        return view('auth.login');
    }

    /**
     * Proses autentikasi login.
     * Input: username, NIP guru, atau NIS siswa.
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $input = trim($request->username);

        // 1. Cari berdasarkan username di tabel user
        $user = User::where('username', $input)->first();

        // 2. Coba NIP (guru)
        if (! $user) {
            $idUserGuru = DB::table('guru')->where('nip', $input)->value('id_user');
            if ($idUserGuru) {
                $user = User::find($idUserGuru);
            }
        }

        // 3. Coba NIS (siswa)
        if (! $user) {
            $idUserSiswa = DB::table('siswa')->where('nis', $input)->value('id_user');
            if ($idUserSiswa) {
                $user = User::find($idUserSiswa);
            }
        }

        // 4. Verifikasi password
        if ($user && Hash::check($request->password, $user->password)) {
            Auth::login($user);
            $request->session()->regenerate();

            return $this->redirectBasedOnRole($user);
        }

        return back()
            ->withInput($request->only('username'))
            ->with('error', 'Nama pengguna / NIP / NIS atau kata sandi tidak cocok.');
    }

    /**
     * Redirect pengguna sesuai rolenya.
     * Role GURU: cek apakah ada di guru_bk → route BK; jika tidak → halaman tidak diizinkan.
     */
    protected function redirectBasedOnRole($user)
    {
        $role = strtoupper($user->role);

        if ($role === 'SISWA') {
            return redirect()->route('siswa.dashboard');
        }

        if ($role === 'GURU') {
            // Cek apakah guru ini terdaftar sebagai Guru BK
            $isGuruBk = DB::table('guru_bk')
                ->join('guru', 'guru.id_guru', '=', 'guru_bk.id_guru')
                ->where('guru.id_user', $user->id_user)
                ->exists();

            if ($isGuruBk) {
                return redirect()->route('bk.dashboard');
            }
        }

        if ($role === 'ADMIN') {
            return redirect()->route('admin.dashboard');
        }

        // Role lain (AKADEMIK, KURIKULUM, dll) — belum diimplementasi di A3
        return redirect()->route('login')->with('error', 'Role Anda belum didukung di modul A3.');
    }

    /**
     * Logout pengguna.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
