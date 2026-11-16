<?php

namespace App\Http\Requests\Rapor;

use Illuminate\Foundation\Http\FormRequest;

class TarikPresensiRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id_periode' => ['required', 'integer', 'exists:periode_nilai,id_periode'],
            'id_kelas' => ['required', 'integer', 'exists:kelas,id_kelas'],
            'dari' => ['required', 'date'],
            'sampai' => ['required', 'date', 'after_or_equal:dari'],
        ];
    }
}
