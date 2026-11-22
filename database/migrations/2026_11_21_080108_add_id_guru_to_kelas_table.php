php -l database/migrations/2026_11_21_080108_add_id_guru_to_kelas_table.php<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            $table->unsignedInteger('id_guru')->nullable()->after('id_kelas');

            $table->foreign('id_guru')
                ->references('id_guru')
                ->on('guru')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            $table->dropForeign(['id_guru']);
            $table->dropColumn('id_guru');
        });
    }
};
