<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ChangePasswordRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'password_lama' => ['required', 'string', 'current_password:web'],
            'password_baru' => ['required', 'string', 'confirmed', 'different:password_lama', Password::defaults()],
        ];
    }

    public function attributes(): array
    {
        return ['password_lama' => 'kata sandi lama', 'password_baru' => 'kata sandi baru'];
    }
}
