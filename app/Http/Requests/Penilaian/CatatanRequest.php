<?php

namespace App\Http\Requests\Penilaian;

use Illuminate\Foundation\Http\FormRequest;

/** Catatan wajib (minta revisi) atau alasan wajib (buka koreksi). */
class CatatanRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'catatan' => [$this->routeIs('*.revisi', '*.koreksi') ? 'required' : 'nullable', 'string', 'min:5', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return ['catatan.required' => 'Catatan/alasan wajib diisi.'];
    }
}
