<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MapelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['admin', 'kurikulum']) ?? false;
    }

    public function rules(): array
    {
        return ['kode' => ['required', 'string', 'max:30', Rule::unique('mapel', 'kode')->ignore($this->route('record')?->id)], 'nama' => ['required', 'string', 'max:255'], 'aktif' => ['required', 'boolean']];
    }
}
