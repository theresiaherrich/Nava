<?php

namespace App\Http\Requests\Penilaian;

use App\Models\Kelas;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AturPeriodeRequest extends FormRequest
{
    /**
     * Tahun tepat 4 digit, 2000–2099 (sama dengan min/max input datetime-local).
     * Dicek pada teks aslinya, bukan setelah konversi zona waktu, agar batas 2000-01-01T00:00+07:00 tetap sah.
     */
    private const TAHUN_2000_2099 = 'regex:/^20\d{2}-\d{2}-\d{2}[T ]\d{2}:\d{2}/';

    public function rules(): array
    {
        return [
            'tgl_buka' => ['required', 'date', self::TAHUN_2000_2099],
            'tgl_tutup' => ['required', 'date', self::TAHUN_2000_2099, 'after_or_equal:tgl_buka'],
            'tingkat' => ['sometimes', 'array', 'min:1'],
            'tingkat.*' => ['distinct', Rule::in(Kelas::TINGKAT)],
        ];
    }

    public function messages(): array
    {
        return [
            'tgl_buka.required' => 'Waktu mulai wajib diisi.',
            'tgl_buka.date' => 'Waktu mulai tidak valid.',
            'tgl_buka.regex' => 'Tahun waktu mulai harus 4 digit, antara 2000 dan 2099.',
            'tgl_tutup.required' => 'Waktu selesai wajib diisi.',
            'tgl_tutup.date' => 'Waktu selesai tidak valid.',
            'tgl_tutup.regex' => 'Tahun waktu selesai harus 4 digit, antara 2000 dan 2099.',
            'tgl_tutup.after_or_equal' => 'Waktu selesai tidak boleh sebelum waktu mulai.',
        ];
    }
}
