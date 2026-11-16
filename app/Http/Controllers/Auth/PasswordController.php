<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;

class PasswordController extends Controller
{
    public function __construct(private AuditService $audit) {}

    public function change(ChangePasswordRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->forceFill([
            'password' => $request->string('password_baru'),
            'must_change_password' => false,
        ])->save();

        $request->session()->regenerate();
        $this->audit->catat('ganti_password', 'user', $user->id_user);

        return $this->ok(['csrf_token' => csrf_token()], 'Kata sandi berhasil diganti.');
    }
}
