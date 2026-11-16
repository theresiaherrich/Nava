<?php

namespace App\Http\Requests\Kurikulum;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MapelRequest extends FormRequest
{
    public function rules(): array
    {
        $id = $this->route('mapel')?->id_mapel;
        $wajib = $this->isMethod('post') ? 'required' : 'sometimes';

        return [
            'kode' => [$wajib, 'string', 'max:30', 'alpha_dash:ascii', Rule::unique('mapel', 'kode')->ignore($id, 'id_mapel')],
            'nama' => [$wajib, 'string', 'max:120'],
            'kelompok' => ['nullable', 'string', 'max:60'],
            'urutan_rapor' => ['sometimes', 'integer', 'between:1,99'],
            'id_agama' => ['nullable', 'integer', 'exists:agama,id_agama'],
            'kktp_default' => ['sometimes', 'numeric', 'between:0,100', 'decimal:0,2'],
            'deskripsi_kuat' => ['nullable', 'string', 'max:255'],
            'deskripsi_penguatan' => ['nullable', 'string', 'max:255'],
            'aktif' => ['sometimes', 'boolean'],
        ];
    }
}
