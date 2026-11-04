<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SemesterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['admin', 'kurikulum']) ?? false;
    }

    public function rules(): array
    {
        return ['tahun_ajaran_id' => ['required', 'integer', 'exists:tahun_ajaran,id'], 'nama' => ['required', Rule::in(['ganjil', 'genap']), Rule::unique('semester', 'nama')->where('tahun_ajaran_id', $this->input('tahun_ajaran_id'))->ignore($this->route('record')?->id)], 'aktif' => ['required', 'boolean']];
    }
}
