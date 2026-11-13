<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function store(Request $request)
    {
        $allowedRoles = [
            'guru_mapel','wali_kelas','guru_bk','budaya_positif','siswa'
        ];
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'username' => ['required','string','max:255','alpha_dash','unique:users,username'],
            'email' => ['required','email','max:255','unique:users,email'],
            'roles' => ['required','array','min:1'],
            'roles.*' => ['required','string',Rule::in($allowedRoles)],
        ]);
        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'username' => $data['username'],
                'email' => $data['email'],
                // Sementara: acak & tidak dibagikan melalui chat/email
                'password' => Hash::make(Str::random(48)),
                'is_active' => true,
            ]);
            $user->syncRoles($data['roles']);
            return $user;
        });
        // Tahap lanjutan: kirim undangan set password aman ke email terverifikasi.
        return response()->json(['id' => $user->id], 201);
    }
}