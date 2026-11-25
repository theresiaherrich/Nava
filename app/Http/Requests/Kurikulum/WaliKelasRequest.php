<?php

namespace App\Http\Requests\Kurikulum;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WaliKelasRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id_guru' => ['required', 'integer', Rule::exists('guru', 'id_guru')->where('aktif', true)],
            'id_semester' => ['nullable', 'integer', 'exists:semester,id_semester'],
        ];
    }
}
