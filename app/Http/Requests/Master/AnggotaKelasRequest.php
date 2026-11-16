<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;

class AnggotaKelasRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id_semester' => ['nullable', 'integer', 'exists:semester,id_semester'],
            'id_siswa' => ['required', 'array', 'min:1', 'max:200'],
            'id_siswa.*' => ['integer', 'distinct', 'exists:siswa,id_siswa'],
        ];
    }
}
