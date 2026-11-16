<?php

namespace App\Http\Requests\Penilaian;

use Illuminate\Foundation\Http\FormRequest;

class KktpRequest extends FormRequest
{
    public function rules(): array
    {
        return ['kktp' => ['present', 'nullable', 'numeric', 'between:0,100', 'decimal:0,2']];
    }
}
