<?php

namespace App\Http\Requests\Penilaian;

use Illuminate\Foundation\Http\FormRequest;

class SalinTpRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id_tp' => ['required', 'array', 'min:1', 'max:10'],
            'id_tp.*' => ['integer', 'distinct'],
            'id_rombel_tujuan' => ['required', 'array', 'min:1', 'max:20'],
            'id_rombel_tujuan.*' => ['integer', 'distinct', 'exists:rombel,id_rombel'],
        ];
    }
}
