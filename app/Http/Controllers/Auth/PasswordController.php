<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

class PasswordController extends Controller
{
    public function forgotForm() { return view('auth.forgot-password'); }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => ['required','email']]);
        Password::sendResetLink($request->only('email'));
        // Respons sama untuk email dikenal maupun tidak (mengurangi enumerasi).
        return back()->with('status', 'Jika email terdaftar, tautan pemulihan akan dikirim.');
    }

    public function resetForm(string $token, Request $request)
    {
        return view('auth.reset-password', [
            'token' => $token, 'email' => $request->query('email', ''),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $data = $request->validate([
            'token' => ['required','string'],
            'email' => ['required','email'],
            'password' => ['required','confirmed',PasswordRule::min(12)->mixedCase()->numbers()->symbols()],
        ]);
        $status = Password::reset($data, function (User $user, string $password) {
            $user->forceFill([
                'password' => Hash::make($password),
                'remember_token' => Str::random(60),
            ])->save();
            event(new PasswordReset($user));
        });
        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', 'Password berhasil direset.')
            : back()->withErrors(['email' => __($status)]);
    }

    public function changePassword(\Illuminate\Http\Request $request)
{
    $request->validate([
        'current_password' => ['required','current_password'],
        'password' => ['required','confirmed',\Illuminate\Validation\Rules\Password::min(12)->mixedCase()->numbers()->symbols()],
    ]);
    $request->user()->update([
        'password' => $request->input('password'), // cast hashed pada User
    ]);
    return back()->with('status', 'Password berhasil diganti.');
}

// Contoh field Blade untuk form ubah password:
// <form method="POST" action="{{ route('password.change') }}">
// @csrf @method('PUT')
// <input type="password" name="current_password">
// <input type="password" name="password">
// <input type="password" name="password_confirmation">
// <button>Ganti password</button></form>
}