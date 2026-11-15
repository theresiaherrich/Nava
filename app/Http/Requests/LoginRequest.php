<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['login' => strtolower(trim((string) $this->login))]);
    }

    public function rules(): array
    {
        return ['login' => ['required', 'string', 'max:255'], 'password' => ['required', 'string', 'max:72']];
    }
}
