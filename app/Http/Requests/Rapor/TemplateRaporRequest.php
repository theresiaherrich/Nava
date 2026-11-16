<?php

namespace App\Http\Requests\Rapor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TemplateRaporRequest extends FormRequest
{
    public function rules(): array
    {
        $wajib = $this->isMethod('post') ? 'required' : 'sometimes';

        return [
            'jenis' => [$wajib, Rule::in(['STS', 'SAS'])],
            'nama' => [$wajib, 'string', 'max:120'],
            'versi' => ['sometimes', 'string', 'max:20'],
            'view' => [$wajib, Rule::in(array_keys(config('siakad.rapor_views')))],
            'judul' => ['sometimes', 'string', 'max:120'],
            'tempat_cetak' => ['nullable', 'string', 'max:60'],
            'tampilkan_deskripsi' => ['sometimes', 'boolean'],
            'aktif' => ['sometimes', 'boolean'],
        ];
    }
}
