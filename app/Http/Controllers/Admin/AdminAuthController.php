<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AdminAuthController extends Controller
{
    public function showLogin()
    {
        if (session()->has('admin_id_user')) return redirect()->route('admin.dashboard');
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'username' => ['required','string','max:50'],
            'password' => ['required','string'],
        ]);

        $user = DB::table('user')->where('username', $data['username'])->where('role', 'ADMIN')->first();
        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages(['username' => 'Username atau kata sandi tidak sesuai.']);
        }

        $request->session()->regenerate();
        $request->session()->put([
            'admin_id_user' => $user->id_user,
            'admin_username' => $user->username,
        ]);

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request)
    {
        $request->session()->forget(['admin_id_user', 'admin_username']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }
}
