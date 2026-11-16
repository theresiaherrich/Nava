<?php

namespace App\Services;

use App\Models\PasswordResetToken;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PasswordResetService
{
    public function __construct(private AuditService $audit) {}

    /**
     * Membuat token reset (plaintext dikembalikan sekali; di database hanya hash-nya).
     * Mengembalikan null bila akun tidak ada, agar respons tidak membocorkan keberadaan akun.
     */
    public function minta(string $username): ?string
    {
        $user = User::where('username', $username)->first();

        if (! $user || ! $user->isAktif()) {
            return null;
        }

        PasswordResetToken::where('id_user', $user->id_user)->delete();

        $plain = Str::random(64);

        PasswordResetToken::create([
            'id_user' => $user->id_user,
            'token_hash' => hash('sha256', $plain),
            'expires_at' => now()->addMinutes((int) config('siakad.reset_token_ttl', 60)),
            'created_at' => now(),
        ]);

        $this->audit->catat('minta_reset_password', 'user', $user->id_user, null, null, null, $user->id_user);

        return $plain;
    }

    public function reset(string $tokenPlain, string $passwordBaru): void
    {
        $token = PasswordResetToken::where('token_hash', hash('sha256', $tokenPlain))
            ->where('expires_at', '>', now())
            ->first();

        if (! $token) {
            throw ValidationException::withMessages(['token' => 'Token tidak valid atau sudah kedaluwarsa.']);
        }

        DB::transaction(function () use ($token, $passwordBaru) {
            $user = User::findOrFail($token->id_user);

            $user->update(['password' => $passwordBaru, 'must_change_password' => false]);

            PasswordResetToken::where('id_user', $user->id_user)->delete();

            $this->audit->catat('reset_password', 'user', $user->id_user, null, null, null, $user->id_user);
        });
    }

    /** Admin membuat kata sandi sementara; ditampilkan sekali dan wajib diganti pengguna. */
    public function resetOlehAdmin(User $target): string
    {
        $plain = Str::random(10);

        DB::transaction(function () use ($target, $plain) {
            $target->update(['password' => $plain, 'must_change_password' => true]);

            $this->audit->catat('reset_password_admin', 'user', $target->id_user, null, null, ['must_change_password' => true]);
        });

        return $plain;
    }

    public function ubahOlehSendiri(User $user, string $sandiLama, string $sandiBaru): void
    {
        if (! Hash::check($sandiLama, $user->password)) {
            throw ValidationException::withMessages(['current_password' => 'Kata sandi saat ini tidak sesuai.']);
        }

        DB::transaction(function () use ($user, $sandiBaru) {
            $user->update(['password' => $sandiBaru, 'must_change_password' => false]);

            $this->audit->catat('ubah_kata_sandi', 'user', $user->id_user, null, null, null, $user->id_user);
        });
    }
}
