<?php

namespace App\Http\Requests\Penilaian;

use Illuminate\Foundation\Http\FormRequest;

class TpRequest extends FormRequest
{
    public function rules(): array
    {
        $wajib = $this->isMethod('post') ? 'required' : 'sometimes';

        return [
            'kode' => ['nullable', 'string', 'max:30'],
            'deskripsi' => [$wajib, 'string', 'min:5', 'max:500'],
            'hanya_sts' => ['sometimes', 'boolean'],
        ];
    }
}
