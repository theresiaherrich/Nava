<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sekolah', function (Blueprint $table) {
            $table->id();
            $table->string('npsn', 8)->unique();
            $table->string('nama');
            $table->text('alamat');
            $table->string('nama_kepala_sekolah');
            $table->timestamps();
        });
        Schema::create('tahun_ajaran', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 9)->unique();
            $table->boolean('aktif')->default(false);
            $table->timestamps();
        });
        Schema::create('semester', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajaran')->restrictOnDelete();
            $table->string('nama', 10);
            $table->boolean('aktif')->default(false);
            $table->timestamps();
            $table->unique(['tahun_ajaran_id', 'nama']);
        });
        Schema::create('kelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('semester_id')->constrained('semester')->restrictOnDelete();
            $table->string('nama', 50);
            $table->unsignedTinyInteger('tingkat');
            $table->string('fase', 1);
            $table->timestamps();
            $table->unique(['semester_id', 'nama']);
            $table->unique(['id', 'semester_id']);
        });
        Schema::create('mapel', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 30)->unique();
            $table->string('nama');
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
        Schema::create('siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->restrictOnDelete();
            $table->string('nis', 30)->unique();
            $table->string('nisn', 10)->nullable()->unique();
            $table->string('nama');
            $table->string('agama', 50)->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
        Schema::create('guru', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->restrictOnDelete();
            $table->string('nama');
            $table->string('nip', 30)->nullable()->unique();
            $table->string('status_kepegawaian', 50);
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
        Schema::create('siswa_kelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->restrictOnDelete();
            $table->foreignId('kelas_id');
            $table->foreignId('semester_id')->constrained('semester')->restrictOnDelete();
            $table->foreign(['kelas_id', 'semester_id'])->references(['id', 'semester_id'])->on('kelas')->restrictOnDelete();
            $table->timestamps();
            $table->unique(['siswa_id', 'semester_id']);
        });
    }

    public function down(): void
    {
        foreach (['siswa_kelas', 'guru', 'siswa', 'mapel', 'kelas', 'semester', 'tahun_ajaran', 'sekolah'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
