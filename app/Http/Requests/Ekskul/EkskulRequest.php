<?php

namespace App\Http\Requests\Ekskul;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EkskulRequest extends FormRequest
{
    public function rules(): array
    {
        $wajib = $this->isMethod('post') ? 'required' : 'sometimes';

        return [
            'nama' => [$wajib, 'string', 'max:120', Rule::unique('ekskul', 'nama')->ignore($this->route('ekskul')?->id_ekskul, 'id_ekskul')],
            'id_pembina' => ['nullable', 'integer', 'exists:guru,id_guru'],
            'aktif' => ['sometimes', 'boolean'],
        ];
    }
}
