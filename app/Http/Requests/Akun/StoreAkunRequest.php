<?php

namespace App\Http\Requests\Akun;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreAkunRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->filled('username')) {
            $this->merge(['username' => mb_strtolower(trim((string) $this->input('username')))]);
        }
    }

    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'min:3', 'max:50', 'alpha_dash:ascii', Rule::unique('user', 'username')],
            'nama' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:100', Rule::unique('user', 'email')],
            'password' => ['nullable', 'string', Password::defaults()],
            'status_akun' => ['sometimes', Rule::in(['aktif', 'belum_aktif'])],
            'must_change_password' => ['sometimes', 'boolean'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['string', 'distinct', Rule::in(array_keys(config('siakad.roles')))],
            'id_guru' => ['nullable', 'integer'],
            'id_siswa' => ['nullable', 'integer'],
            'id_orang_tua' => ['nullable', 'integer'],
        ];
    }
}
