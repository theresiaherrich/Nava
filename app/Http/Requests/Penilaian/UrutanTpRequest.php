<?php

namespace App\Http\Requests\Penilaian;

use Illuminate\Foundation\Http\FormRequest;

class UrutanTpRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id_tp' => ['required', 'array', 'list', 'min:1', 'max:10'],
            'id_tp.*' => ['integer', 'distinct'],
        ];
    }
}
