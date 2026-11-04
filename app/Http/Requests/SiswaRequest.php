<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SiswaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['admin', 'kurikulum']) ?? false;
    }

    public function rules(): array
    {
        return ['nis' => ['required', 'string', 'max:30', Rule::unique('siswa', 'nis')->ignore($this->route('record')?->id)], 'nisn' => ['nullable', 'digits:10', Rule::unique('siswa', 'nisn')->ignore($this->route('record')?->id)], 'nama' => ['required', 'string', 'max:255'], 'agama' => ['nullable', 'string', 'max:50'], 'aktif' => ['required', 'boolean'], 'user_id' => ['prohibited']];
    }
}
