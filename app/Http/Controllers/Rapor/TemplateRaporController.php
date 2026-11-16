<?php

namespace App\Http\Controllers\Rapor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Rapor\TemplateRaporRequest;
use App\Models\TemplateRapor;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/** Format/template rapor. Hanya satu template aktif per jenis (STS/SAS). */
class TemplateRaporController extends Controller
{
    public function __construct(private AuditService $audit) {}

    public function index(): JsonResponse
    {
        return $this->ok(['template' => TemplateRapor::orderBy('jenis')->orderByDesc('id_template')->get(), 'view_tersedia' => config('siakad.rapor_views')]);
    }

    public function store(TemplateRaporRequest $request): JsonResponse
    {
        return $this->created($this->simpan(new TemplateRapor, $request->validated()), 'Template dibuat.');
    }

    public function update(TemplateRaporRequest $request, TemplateRapor $template): JsonResponse
    {
        return $this->ok($this->simpan($template, $request->validated()), 'Template diperbarui.');
    }

    private function simpan(TemplateRapor $t, array $data): TemplateRapor
    {
        return DB::transaction(function () use ($t, $data) {
            $sebelum = $t->exists ? $t->only(array_keys($data)) : null;
            $t->fill($data)->save();
            if ($t->aktif) {
                TemplateRapor::where('jenis', $t->jenis)->whereKeyNot($t->id_template)->update(['aktif' => false]);
            }
            $this->audit->catat($sebelum ? 'ubah_template_rapor' : 'buat_template_rapor', 'template_rapor', $t->id_template, $sebelum, $data);

            return $t->fresh();
        });
    }
}
