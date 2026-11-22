<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! $this->hasUniqueIndex('sakit', 'uq_sakit_id_presensi')) {
            
            Schema::table('sakit', function (Blueprint $table) {
                $table->unique(
                    'id_presensi',
                    'uq_sakit_id_presensi'

                );
            });
        }

        if (! $this->hasUniqueIndex('izin', 'uq_izin_id_presensi')) {
            Schema::table('izin', function (Blueprint $table) {
                $table->unique(
                    'id_presensi',
                    'uq_izin_id_presensi'
                );
            });
        }
    }

    public function down(): void
    {
        // Indeks tidak dihapus karena sudah ada
        // sebelum migration ini dijalankan.
    }

    private function hasUniqueIndex(
        string $table,
        string $indexName
    ): bool {
        $indexes = DB::select(
            "SHOW INDEX FROM `{$table}` WHERE Key_name = ?",
            [$indexName]
        );

        foreach ($indexes as $index) {
            if ((int) $index->Non_unique === 0) {
                return true;
            }
        }

        return false;
    }
};
