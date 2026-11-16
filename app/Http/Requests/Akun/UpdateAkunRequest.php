<?php

namespace App\Http\Requests\Akun;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAkunRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->filled('username')) {
            $this->merge(['username' => mb_strtolower(trim((string) $this->input('username')))]);
        }
    }

    public function rules(): array
    {
        $id = $this->route('user')->id_user;

        return [
            'username' => ['sometimes', 'string', 'min:3', 'max:50', 'alpha_dash:ascii', Rule::unique('user', 'username')->ignore($id, 'id_user')],
            'nama' => ['sometimes', 'string', 'max:120'],
            'email' => ['sometimes', 'nullable', 'email', 'max:100', Rule::unique('user', 'email')->ignore($id, 'id_user')],
        ];
    }
}
