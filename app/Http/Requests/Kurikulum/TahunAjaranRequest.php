<?php

namespace App\Http\Requests\Kurikulum;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TahunAjaranRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'regex:/^\d{4}\/\d{4}$/', Rule::unique('tahun_ajaran', 'nama')->ignore($this->route('tahunAjaran')?->id_tahun_ajaran, 'id_tahun_ajaran'),
                function (string $attr, mixed $v, \Closure $fail) {
                    [$a, $b] = array_map('intval', explode('/', (string) $v) + [0, 0]);
                    if ($b !== $a + 1) {
                        $fail('Format tahun ajaran harus YYYY/YYYY+1, mis. 2026/2027.');
                    }
                }],
        ];
    }
}
