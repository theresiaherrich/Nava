<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RekapNilaiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['admin', 'kurikulum', 'guru', 'wali_kelas']) ?? false;
    }

    public function rules(): array
    {
        return [
            'periode_id' => ['required', 'integer', 'exists:periode_nilai,id'],
            'kelas_id' => ['nullable', 'integer', 'exists:kelas,id'],
            'rombel_id' => ['nullable', 'integer', 'exists:rombel,id'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'scope' => [$this->routeIs('rekap-nilai.export') ? 'required' : 'sometimes', 'in:semua'],
        ];
    }
}
