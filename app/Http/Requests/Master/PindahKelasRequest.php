<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;

class PindahKelasRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id_kelas' => ['required', 'integer', 'exists:kelas,id_kelas'],
            'alasan' => ['required', 'string', 'max:500'],
        ];
    }
}
