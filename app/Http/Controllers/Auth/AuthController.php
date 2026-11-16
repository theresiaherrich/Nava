<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\SwitchRoleRequest;
use App\Http\Resources\AkunResource;
use App\Models\User;
use App\Services\AuditService;
use App\Support\AcademicAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function __construct(private AuditService $audit) {}

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('username', Str::lower(trim($request->string('username'))))->first();

        // Hash::check tetap dijalankan agar waktu respons tidak membocorkan username yang ada.
        $cocok = Hash::check($request->string('password'), $user?->password ?? '$2y$12$'.str_repeat('x', 53));

        if (! $user || ! $cocok) {
            return response()->json(['message' => 'Username atau kata sandi salah.', 'code' => 'INVALID_CREDENTIALS'], 401);
        }

        if ($user->status_akun !== 'aktif') {
            $belum = $user->status_akun === 'belum_aktif';

            return response()->json([
                'message' => $belum
                    ? 'Akun Anda belum diaktifkan. Hubungi operator sekolah.'
                    : 'Akun Anda dinonaktifkan. Hubungi operator sekolah.',
                'code' => $belum ? 'ACCOUNT_NOT_ACTIVATED' : 'ACCOUNT_DISABLED',
            ], 403);
        }

        if ($user->kodeRoles() === []) {
            return response()->json(['message' => 'Akun Anda belum memiliki peran. Hubungi operator sekolah.', 'code' => 'NO_ROLE'], 403);
        }

        Auth::login($user);
        $request->session()->regenerate();
        AcademicAccess::setPeranAktif($user->kodeRoles()[0]);

        $user->forceFill(['last_login_at' => now()])->save();
        $this->audit->catat('login', 'user', $user->id_user, null, null, null, $user->id_user);

        return $this->ok($this->sesi($user), 'Login berhasil.');
    }

    public function logout(Request $request): JsonResponse
    {
        if ($id = Auth::id()) {
            $this->audit->catat('logout', 'user', $id);
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return $this->ok(['csrf_token' => csrf_token()], 'Logout berhasil.');
    }

    public function me(Request $request): JsonResponse
    {
        return $this->ok($this->sesi($request->user()));
    }

    public function switchRole(SwitchRoleRequest $request): JsonResponse
    {
        $sebelum = AcademicAccess::peranAktif();
        AcademicAccess::setPeranAktif($request->string('peran'));
        $this->audit->catat('ganti_peran', 'user', Auth::id(), ['peran_aktif' => $sebelum], ['peran_aktif' => (string) $request->string('peran')]);

        return $this->ok($this->sesi($request->user()), 'Peran aktif diganti.');
    }

    private function sesi(User $user): array
    {
        return [
            'user' => AkunResource::make($user)->resolve(),
            'peran_aktif' => AcademicAccess::peranAktif(),
            'must_change_password' => $user->must_change_password,
            'csrf_token' => csrf_token(),
        ];
    }
}
