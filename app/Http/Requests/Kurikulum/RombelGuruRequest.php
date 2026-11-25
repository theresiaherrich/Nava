<?php

namespace App\Http\Requests\Kurikulum;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RombelGuruRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id_guru' => ['present', 'array', 'max:5'],
            'id_guru.*' => ['integer', 'distinct', Rule::exists('guru', 'id_guru')->where('aktif', true)],
        ];
    }
}
