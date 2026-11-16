<?php

namespace App\Http\Requests\Akun;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SetStatusRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'status_akun' => ['required', Rule::in(['aktif', 'nonaktif', 'belum_aktif'])],
            'alasan' => ['required_if:status_akun,nonaktif', 'nullable', 'string', 'max:500'],
        ];
    }
}
