<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrangTuaRequest extends FormRequest
{
    public function rules(): array
    {
        $wajib = $this->isMethod('post') ? 'required' : 'sometimes';

        return [
            'nama' => [$wajib, 'string', 'max:120'],
            'jenis_kelamin' => ['nullable', Rule::in(['L', 'P'])],
            'no_telepon' => ['nullable', 'string', 'max:25'],
            'alamat' => ['nullable', 'string', 'max:500'],
        ];
    }
}
