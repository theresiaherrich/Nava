<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wali_kelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelas_id')->constrained('kelas')->restrictOnDelete();
            $table->foreignId('guru_id')->constrained('guru')->restrictOnDelete();
            $table->timestamps();
            // ASUMSI: satu wali aktif per kelas. Konfirmasi ke Kurikulum sebelum rapor resmi.
            $table->unique('kelas_id');
        });

        Schema::create('rombel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('semester_id')->constrained('semester')->restrictOnDelete();
            $table->foreignId('mapel_id')->constrained('mapel')->restrictOnDelete();
            $table->string('nama', 100);
            $table->timestamps();
            $table->unique(['semester_id', 'mapel_id', 'nama']);
        });

        Schema::create('guru_rombel', function (Blueprint $table) {
            $table->foreignId('rombel_id')->constrained('rombel')->cascadeOnDelete();
            $table->foreignId('guru_id')->constrained('guru')->restrictOnDelete();
            $table->timestamps();
            $table->primary(['rombel_id', 'guru_id']);
        });

        Schema::create('siswa_rombel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rombel_id')->constrained('rombel')->restrictOnDelete();
            $table->foreignId('siswa_kelas_id')->constrained('siswa_kelas')->restrictOnDelete();
            $table->timestamps();
            $table->unique(['rombel_id', 'siswa_kelas_id']);
        });
    }

    public function down(): void
    {
        foreach (['siswa_rombel', 'guru_rombel', 'rombel', 'wali_kelas'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
