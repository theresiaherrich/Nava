<?php

namespace App\Http\Controllers\A3_BK\Guru;

use App\Http\Controllers\A3_BK\Concerns\KonteksGuruBk;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Profil Guru BK — lihat, ubah data diri, dan ubah kata sandi.
 */
class ProfilController extends Controller
{
    use KonteksGuruBk;

    public function index()
    {
        $ctx = $this->getKonteksGuru();

        // Waktu terakhir ubah password dari log_aktivitas
        $logPassword = DB::table('log_aktivitas')
            ->where('id_user', $ctx['user']->id_user)
            ->where('aksi', 'ubah_password')
            ->orderByDesc('created_at')
            ->first();

        return view('bk.guru.profil', array_merge($ctx, compact('logPassword')));
    }

    public function update(Request $request)
    {
        $ctx = $this->getKonteksGuru();

        $request->validate([
            'nama' => 'required|string|max:100',
            'email' => 'nullable|email|max:100',
            'no_telepon' => 'nullable|string|max:25',
            'jadwal_layanan' => 'nullable|string|max:100',
        ]);

        DB::transaction(function () use ($request, $ctx) {
            $ctx['guru']->forceFill(['nama' => $request->nama])->save();
            $ctx['guruBk']->update($request->only('email', 'no_telepon', 'jadwal_layanan'));
        });

        return redirect()->route('bk.profil')->with('success', 'Profil berhasil diperbarui.');
    }

    public function ubahPassword(Request $request)
    {
        $ctx = $this->getKonteksGuru();

        $request->validate([
            'password_lama' => 'required|string',
            'password_baru' => 'required|string|min:8|confirmed',
        ]);

        if (! Hash::check($request->password_lama, $ctx['user']->password)) {
            return back()->withErrors(['password_lama' => 'Kata sandi lama tidak sesuai.']);
        }

        $ctx['user']->update(['password' => Hash::make($request->password_baru)]);

        DB::table('log_aktivitas')->insert([
            'id_user' => $ctx['user']->id_user,
            'aksi' => 'ubah_password',
            'nama_entitas' => 'user',
            'entitas_id' => $ctx['user']->id_user,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('bk.profil')->with('success', 'Kata sandi berhasil diubah.');
    }
}
