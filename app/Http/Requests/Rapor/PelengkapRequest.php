<?php

namespace App\Http\Requests\Rapor;

use Illuminate\Foundation\Http\FormRequest;

class PelengkapRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id_periode' => ['required', 'integer', 'exists:periode_nilai,id_periode'],
            'id_kelas' => ['required', 'integer', 'exists:kelas,id_kelas'],
            'items' => ['required', 'array', 'list', 'min:1', 'max:60'],
            'items.*.id_riwayat_kelas' => ['required', 'integer', 'distinct'],
            'items.*.sakit' => ['sometimes', 'nullable', 'integer', 'between:0,200'],
            'items.*.izin' => ['sometimes', 'nullable', 'integer', 'between:0,200'],
            'items.*.alpha' => ['sometimes', 'nullable', 'integer', 'between:0,200'],
            // Teks polos (dirender ter-escape di PDF); batasi panjang.
            'items.*.catatan_wali' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'items.*.kokurikuler' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }
}
