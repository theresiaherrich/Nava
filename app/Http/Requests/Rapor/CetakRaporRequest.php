<?php

namespace App\Http\Requests\Rapor;

use Illuminate\Foundation\Http\FormRequest;

class CetakRaporRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id_periode' => ['required', 'integer', 'exists:periode_nilai,id_periode'],
            'id_riwayat_kelas' => ['required_without:id_kelas', 'integer', 'exists:riwayat_kelas,id_riwayat_kelas'],
            'id_kelas' => ['required_without:id_riwayat_kelas', 'integer', 'exists:kelas,id_kelas'],
            'format' => ['sometimes', 'in:pdf,zip'],
        ];
    }
}
