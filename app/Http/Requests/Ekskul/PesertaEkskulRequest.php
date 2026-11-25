<?php

namespace App\Http\Requests\Ekskul;

use Illuminate\Foundation\Http\FormRequest;

class PesertaEkskulRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id_riwayat_kelas' => ['required', 'array', 'min:1', 'max:300'],
            'id_riwayat_kelas.*' => ['integer', 'distinct', 'exists:riwayat_kelas,id_riwayat_kelas'],
        ];
    }
}
