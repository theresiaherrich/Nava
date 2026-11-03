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
     * Tampilkan halaman login split-screen.
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        return view('auth.login');
    }

    /**
     * Proses autentikasi login Guru BK dan Siswa.
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $input = trim($request->username);

        // 1. Cari user berdasarkan username di tabel user
        $user = User::where('username', $input)->first();

        // 2. Jika tidak ditemukan, coba cari lewat NIP di tabel guru
        if (!$user) {
            $idUserGuru = DB::table('guru')->where('nip', $input)->value('id_user');
            if ($idUserGuru) {
                $user = User::find($idUserGuru);
            }
        }

        // 3. Jika masih tidak ditemukan, coba cari lewat NIS di tabel siswa
        if (!$user) {
            $idUserSiswa = DB::table('siswa')->where('nis', $input)->value('id_user');
            if ($idUserSiswa) {
                $user = User::find($idUserSiswa);
            }
        }

        // 4. Verifikasi user dan password
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
     * Redirect pengguna sesuai dengan rolenya.
     */
    protected function redirectBasedOnRole($user)
    {
        $role = strtoupper($user->role);

        if (in_array($role, ['BK', 'GURU'])) {
            return redirect()->route('bk.guru-dashboard');
        } elseif ($role === 'SISWA') {
            return redirect()->route('siswa.dashboard');
        }

        return redirect('/');
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

