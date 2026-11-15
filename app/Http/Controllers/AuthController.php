<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function csrf(Request $request): JsonResponse
    {
        return response()->json(['csrf_token' => $request->session()->token()]);
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = DB::transaction(function () use ($request) {
            $user = User::create($request->safe()->only(['name', 'username', 'email', 'password']));
            AuditService::record('register', 'users', $user->id, null, ['status' => 'menunggu_persetujuan'], actorId: $user->id);

            return $user;
        });

        return response()->json(['message' => 'Pendaftaran berhasil. Tunggu persetujuan admin sekolah.', 'data' => ['id' => $user->id]], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $field = str_contains($request->validated('login'), '@') ? 'email' : 'username';
        $ok = Auth::attempt([$field => $request->validated('login'), 'password' => $request->validated('password'), 'is_active' => true]);
        if (! $ok) {
            return response()->json(['message' => 'Login gagal. Periksa identitas, password, atau persetujuan akun.'], 401);
        }
        $request->session()->regenerate();
        AuditService::record('login', 'users', $request->user()->id);

        return response()->json(['message' => 'Login berhasil.', 'data' => $request->user()->load('roles'), 'csrf_token' => csrf_token()]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['data' => $request->user()->load('roles')]);
    }

    public function logout(Request $request): JsonResponse
    {
        AuditService::record('logout', 'users', $request->user()->id);
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logout berhasil.']);
    }
}
