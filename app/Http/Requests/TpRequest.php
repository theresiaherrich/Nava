<?php

namespace App\Http\Requests;

use App\Services\PenilaianAccess;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TpRequest extends FormRequest
{
    public function authorize(): bool
    {
        $rombel = $this->route('record') ?? $this->route('tp')?->rombel;

        return $this->user() !== null && $rombel !== null && PenilaianAccess::bisaKelolaRombel($this->user(), $rombel);
    }

    public function rules(): array
    {
        $rombelId = $this->route('record')?->id ?? $this->route('tp')?->rombel_id;

        return [
            'nomor_urut' => ['required', 'integer', 'min:1', 'max:'.PenilaianAccess::MAKS_TP,
                Rule::unique('tp', 'nomor_urut')->where('rombel_id', $rombelId)->ignore($this->route('tp')?->id)],
            'deskripsi' => ['required', 'string', 'min:5', 'max:500'],
            'masuk_sts' => ['sometimes', 'boolean'],
            'is_sas' => ['sometimes', 'boolean'],
        ];
    }
}
