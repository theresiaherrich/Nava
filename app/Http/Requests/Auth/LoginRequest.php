<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'username.required' => 'Nama pengguna wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
            'username.max' => 'Nama pengguna atau kata sandi salah.',
            'password.max' => 'Nama pengguna atau kata sandi salah.',
            'string' => 'Nama pengguna atau kata sandi salah.',
        ];
    }
}
