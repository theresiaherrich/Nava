<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HubungkanOrangTuaRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id_orang_tua' => ['required', 'integer', 'exists:orang_tua,id_orang_tua'],
            'hubungan' => ['required', Rule::in(['Ayah', 'Ibu', 'Wali'])],
        ];
    }
}
