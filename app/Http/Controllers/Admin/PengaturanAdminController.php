<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PengaturanAdminController extends Controller
{
    private function actorId(): int
    {
        return (int) session('admin_id_user');
    }

    public function index()
    {
        $user = DB::table('user')
            ->where('id_user', $this->actorId())
            ->first();

        $admin = DB::table('admin')
            ->where('id_user', $this->actorId())
            ->first();

        return view('admin.settings.index', compact('user', 'admin'));
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = DB::table('user')
            ->where('id_user', $this->actorId())
            ->first();

        if (! $user || ! Hash::check($data['current_password'], $user->password)) {
            return back()->withErrors([
                'current_password' => 'Kata sandi saat ini tidak sesuai.',
            ]);
        }

        DB::table('user')
            ->where('id_user', $this->actorId())
            ->update([
                'password' => Hash::make($data['password']),
                'must_change_password' => 0,
            ]);

        return back()->with('success', 'Kata sandi admin berhasil diperbarui.');
    }
}
