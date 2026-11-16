<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class KelasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['admin', 'kurikulum']) ?? false;
    }

    public function rules(): array
    {
        return ['semester_id' => ['required', 'integer', 'exists:semester,id'], 'nama' => ['required', 'string', 'max:50', Rule::unique('kelas', 'nama')->where('semester_id', $this->input('semester_id'))->ignore($this->route('record')?->id)], 'tingkat' => ['required', 'integer', Rule::in([10, 11, 12])], 'fase' => ['required', Rule::in(['E', 'F'])]];
    }

    public function after(): array
    {
        return [function (Validator $validator) {
            if (in_array((int) $this->tingkat, [10, 11, 12]) && $this->fase !== ((int) $this->tingkat === 10 ? 'E' : 'F')) {
                $validator->errors()->add('fase', 'Kelas X fase E; kelas XI dan XII fase F.');
            }
        }];
    }
}
