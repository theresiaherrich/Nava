<?php

namespace App\Http\Requests\Penilaian;

use App\Services\PenilaianAccess;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Body simpan/pratinjau nilai (autosave satu sel maupun tempel dari Excel):
 * { id_periode, items: [{ id_siswa_rombel, id_tp, nilai|null }] }
 * null = kosongkan sel. Desimal memakai titik, maks. 2 angka di belakang koma.
 */
class NilaiItemsRequest extends FormRequest
{
    /** Otorisasi (guru pengampu) dijalankan sebelum validasi body. */
    public function authorize(): bool
    {
        PenilaianAccess::pastikanPengampu($this->route('rombel'));

        return true;
    }

    public function rules(): array
    {
        return [
            'id_periode' => ['required', 'integer', 'exists:periode_nilai,id_periode'],
            'items' => ['required', 'array', 'list', 'min:1', 'max:1000'],
            'items.*' => ['required', 'array:id_siswa_rombel,id_tp,nilai'],
            'items.*.id_siswa_rombel' => ['required', 'integer'],
            'items.*.id_tp' => ['required', 'integer'],
            'items.*.nilai' => ['present', 'nullable', 'numeric', 'between:0,100', 'decimal:0,2'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $v) {
            $lihat = [];
            foreach ((array) $this->input('items', []) as $i => $item) {
                $k = ($item['id_siswa_rombel'] ?? '').':'.($item['id_tp'] ?? '');
                if (isset($lihat[$k])) {
                    $v->errors()->add("items.{$i}.id_tp", 'Pasangan siswa dan TP tidak boleh berulang.');
                }
                $lihat[$k] = true;
            }
        }];
    }

    /** @return list<array{id_siswa_rombel:int,id_tp:int,nilai:?float}> */
    public function items(): array
    {
        return array_map(fn ($i) => [
            'id_siswa_rombel' => (int) $i['id_siswa_rombel'],
            'id_tp' => (int) $i['id_tp'],
            'nilai' => $i['nilai'] === null ? null : round((float) $i['nilai'], 2),
        ], $this->validated('items'));
    }
}
