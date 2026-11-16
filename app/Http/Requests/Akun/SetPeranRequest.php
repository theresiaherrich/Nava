<?php

namespace App\Http\Requests\Akun;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SetPeranRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['string', 'distinct', Rule::in(array_keys(config('siakad.roles')))],
            'id_guru' => ['nullable', 'integer'],
            'id_siswa' => ['nullable', 'integer'],
            'id_orang_tua' => ['nullable', 'integer'],
        ];
    }
}
