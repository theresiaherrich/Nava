<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemplateRapor extends Model
{
    protected $table = 'template_rapor';

    protected $primaryKey = 'id_template';

    public $timestamps = false;

    protected $fillable = ['id_sekolah', 'jenis', 'versi', 'nama', 'identitas_cetak', 'lokasi_template'];
}
