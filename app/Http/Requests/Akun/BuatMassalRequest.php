<?php

namespace App\Http\Requests\Akun;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BuatMassalRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'jenis' => ['required', Rule::in(['siswa', 'guru', 'orang_tua'])],
            'ids' => ['nullable', 'array', 'max:2000'],
            'ids.*' => ['integer', 'distinct'],
            'format' => ['sometimes', Rule::in(['json', 'xlsx'])],
        ];
    }
}
