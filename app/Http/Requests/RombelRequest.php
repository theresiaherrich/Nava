<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RombelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['admin', 'kurikulum']) ?? false;
    }

    public function rules(): array
    {
        $id = $this->route('record')?->id;

        return [
            'semester_id' => ['required', 'integer', 'exists:semester,id'],
            'mapel_id' => ['required', 'integer', 'exists:mapel,id'],
            'nama' => ['required', 'string', 'max:100', Rule::unique('rombel', 'nama')
                ->where(fn ($q) => $q->where('semester_id', $this->input('semester_id'))->where('mapel_id', $this->input('mapel_id')))
                ->ignore($id)],
        ];
    }
}
