<?php

namespace App\Http\Requests\Master;

use App\Models\Siswa;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SiswaRequest extends FormRequest
{
    public function rules(): array
    {
        $id = $this->route('siswa')?->id_siswa;
        $wajib = $this->isMethod('post') ? 'required' : 'sometimes';

        return [
            'nis' => [$wajib, 'string', 'max:30', Rule::unique('siswa', 'nis')->ignore($id, 'id_siswa')],
            'nisn' => ['nullable', 'digits:10', Rule::unique('siswa', 'nisn')->ignore($id, 'id_siswa')],
            'nama' => [$wajib, 'string', 'max:120'],
            'jenis_kelamin' => ['nullable', Rule::in(['L', 'P'])],
            'id_agama' => ['nullable', 'integer', 'exists:agama,id_agama'],
            'tempat_lahir' => ['nullable', 'string', 'max:60'],
            'tanggal_lahir' => ['nullable', 'date', 'before:today'],
            'alamat' => ['nullable', 'string', 'max:500'],
            'tahun_masuk' => ['nullable', 'integer', 'between:2000,2100'],
            'status' => ['sometimes', Rule::in(Siswa::STATUS)],
        ];
    }
}
