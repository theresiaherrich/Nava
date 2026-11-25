<?php

namespace App\Http\Requests\Ekskul;

use App\Models\PesertaEkskul;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PredikatEkskulRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'list', 'min:1', 'max:300'],
            'items.*.id_peserta_ekskul' => ['required', 'integer', 'distinct'],
            'items.*.predikat' => ['present', 'nullable', Rule::in(PesertaEkskul::PREDIKAT)],
            'items.*.catatan' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }
}
