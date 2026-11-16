<?php

namespace App\Http\Requests\Penilaian;

use App\Models\Kelas;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AturPeriodeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'tgl_buka' => ['required', 'date'],
            'tgl_tutup' => ['required', 'date', 'after:tgl_buka'],
            'tingkat' => ['sometimes', 'array', 'min:1'],
            'tingkat.*' => ['distinct', Rule::in(Kelas::TINGKAT)],
        ];
    }
}
