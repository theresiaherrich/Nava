<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class TahunAjaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['admin', 'kurikulum']) ?? false;
    }

    public function rules(): array
    {
        return ['nama' => ['required', 'regex:/^\d{4}\/\d{4}$/', Rule::unique('tahun_ajaran', 'nama')->ignore($this->route('record')?->id)], 'aktif' => ['required', 'boolean']];
    }

    public function after(): array
    {
        return [function (Validator $validator) {
            if (preg_match('/^(\d{4})\/(\d{4})$/', (string) $this->nama, $m) && (int) $m[2] !== (int) $m[1] + 1) {
                $validator->errors()->add('nama', 'Tahun akhir harus satu tahun setelah tahun awal.');
            }
        }];
    }
}
