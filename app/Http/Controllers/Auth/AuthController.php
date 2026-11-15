<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(private AccountService $accounts) {}

    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:120'],
            'username' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:user,username'],
            'email' => ['nullable', 'email', 'max:100', 'unique:user,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'peran' => ['required', 'in:SISWA,GURU,ORANG_TUA'],
            'nip' => ['nullable', 'string', 'max:30', 'unique:guru,nip'],
        ]);

        $this->accounts->daftar($data);

        return response()->json([
            'message' => 'Pendaftaran diterima. Akun akan aktif setelah disetujui admin sekolah.',
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('username', $data['username'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages(['username' => 'Username atau kata sandi salah.']);
        }

        if (! $user->isAktif()) {
            return response()->json([
                'message' => $user->status_akun === 'pending'
                    ? 'Akun Anda menunggu persetujuan admin sekolah.'
                    : 'Akun Anda nonaktif. Hubungi operator sekolah.',
            ], 403);
        }

        Auth::login($user);
        $request->session()->regenerate();

        $user->forceFill(['last_login_at' => now()])->save();

        $peran = $user->kodeRoles();
        $request->session()->put('active_role', count($peran) === 1 ? $peran[0] : null);

        return response()->json($this->payload($user, $request));
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Berhasil keluar.']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json($this->payload($request->user(), $request));
    }

    /** Dipakai saat akun punya beberapa peran (dropdown "Pilih Peran" / "Ganti Peran"). */
    public function switchRole(Request $request): JsonResponse
    {
        $data = $request->validate(['role' => ['required', 'string']]);
        $user = $request->user();

        if (! $user->hasRole($data['role'])) {
            return response()->json(['message' => 'Anda tidak memiliki peran tersebut.'], 403);
        }

        $request->session()->put('active_role', $data['role']);

        return response()->json($this->payload($user, $request));
    }

    private function payload(User $user, Request $request): array
    {
        return [
            'id_user' => $user->id_user,
            'username' => $user->username,
            'nama' => $user->nama,
            'email' => $user->email,
            'status_akun' => $user->status_akun,
            'must_change_password' => $user->must_change_password,
            'roles' => $user->roles()->get(['kode', 'nama'])
                ->map(fn ($r) => ['kode' => $r->kode, 'nama' => $r->nama])
                ->values(),
            'active_role' => $request->session()->get('active_role'),
            'profil' => [
                'id_siswa' => $user->siswa?->id_siswa,
                'id_guru' => $user->guru?->id_guru,
            ],
        ];
    }
}
