<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GuruRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['admin', 'kurikulum']) ?? false;
    }

    public function rules(): array
    {
        return ['nama' => ['required', 'string', 'max:255'], 'nip' => ['nullable', 'string', 'max:30', Rule::unique('guru', 'nip')->ignore($this->route('record')?->id)], 'status_kepegawaian' => ['required', 'string', 'max:50'], 'aktif' => ['required', 'boolean'], 'user_id' => ['prohibited']];
    }
}
