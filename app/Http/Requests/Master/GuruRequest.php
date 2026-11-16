<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GuruRequest extends FormRequest
{
    public function rules(): array
    {
        $id = $this->route('guru')?->id_guru;
        $wajib = $this->isMethod('post') ? 'required' : 'sometimes';

        return [
            'status_kepegawaian' => [$wajib, Rule::in(['ASN', 'GTT'])],
            // NIP wajib untuk ASN, boleh kosong untuk GTT.
            'nip' => ['nullable', 'required_if:status_kepegawaian,ASN', 'string', 'max:30', Rule::unique('guru', 'nip')->ignore($id, 'id_guru')],
            'nama' => [$wajib, 'string', 'max:120'],
            'jenis_kelamin' => ['nullable', Rule::in(['L', 'P'])],
            'id_agama' => ['nullable', 'integer', 'exists:agama,id_agama'],
            'no_telepon' => ['nullable', 'string', 'max:25'],
            'aktif' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return ['nip.required_if' => 'NIP wajib diisi untuk guru ASN.'];
    }
}
