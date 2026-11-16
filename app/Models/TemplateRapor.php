<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemplateRapor extends Model
{
    protected $table = 'template_rapor';

    protected $primaryKey = 'id_template';

    protected $fillable = ['jenis', 'nama', 'versi', 'view', 'judul', 'tempat_cetak', 'tampilkan_deskripsi', 'aktif'];

    protected function casts(): array
    {
        return ['aktif' => 'boolean', 'tampilkan_deskripsi' => 'boolean'];
    }

    public static function aktifUntuk(string $jenis): ?self
    {
        return static::where('jenis', $jenis)->where('aktif', true)->orderByDesc('id_template')->first();
    }
}
