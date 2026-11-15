<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\PasswordResetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PasswordController extends Controller
{
    public function __construct(private PasswordResetService $passwords) {}

    public function forgot(Request $request): JsonResponse
    {
        $data = $request->validate(['username' => ['required', 'string']]);

        $token = $this->passwords->minta($data['username']);

        $respons = ['message' => 'Jika akun ditemukan, instruksi reset kata sandi akan dikirim.'];

        // Hanya development. Token seharusnya dikirim lewat notifikasi, bukan respons API.
        if ($token && config('siakad.expose_reset_token')) {
            $respons['token_dev'] = $token;
        }

        return response()->json($respons);
    }

    public function reset(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'size:64'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $this->passwords->reset($data['token'], $data['password']);

        return response()->json(['message' => 'Kata sandi berhasil diubah. Silakan masuk kembali.']);
    }

    public function change(Request $request): JsonResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ]);

        $this->passwords->ubahOlehSendiri($request->user(), $data['current_password'], $data['password']);

        return response()->json(['message' => 'Kata sandi berhasil diubah.']);
    }
}
