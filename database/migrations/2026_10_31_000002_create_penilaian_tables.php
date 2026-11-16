<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('periode_nilai', function (Blueprint $table) {
            $table->id();
            $table->foreignId('semester_id')->constrained('semester')->restrictOnDelete();
            $table->enum('jenis', ['STS', 'SAS']);
            $table->unsignedTinyInteger('tingkat');
            $table->dateTime('tanggal_buka');
            $table->dateTime('tanggal_tutup');
            $table->boolean('dikunci')->default(false);
            $table->timestamps();
            $table->unique(['semester_id', 'jenis', 'tingkat']);
        });

        Schema::create('tp', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rombel_id')->constrained('rombel')->restrictOnDelete();
            $table->unsignedTinyInteger('nomor_urut');
            $table->string('deskripsi', 500);
            $table->boolean('masuk_sts')->default(false);
            $table->boolean('is_sas')->default(false);
            $table->timestamps();
            $table->unique(['rombel_id', 'nomor_urut']);
        });

        Schema::create('nilai_tp', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tp_id')->constrained('tp')->restrictOnDelete();
            $table->foreignId('siswa_rombel_id')->constrained('siswa_rombel')->restrictOnDelete();
            $table->decimal('skor', 5, 2)->nullable(); // null = belum diisi, BUKAN 0
            $table->foreignId('diubah_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['tp_id', 'siswa_rombel_id']);
        });

        Schema::create('paket_nilai', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rombel_id')->constrained('rombel')->restrictOnDelete();
            $table->foreignId('periode_id')->constrained('periode_nilai')->restrictOnDelete();
            $table->foreignId('kelas_id')->constrained('kelas')->restrictOnDelete();
            $table->enum('status', ['draft', 'dikirim', 'perlu_perbaikan', 'disetujui', 'dikunci'])->default('draft');
            $table->foreignId('dikirim_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('waktu_kirim')->nullable();
            $table->foreignId('dikunci_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('waktu_kunci')->nullable();
            $table->timestamps();
            $table->unique(['rombel_id', 'periode_id', 'kelas_id']);
        });

        Schema::create('verifikasi_nilai', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paket_id')->constrained('paket_nilai')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->enum('keputusan', ['disetujui', 'perlu_revisi', 'dikunci', 'buka_koreksi']);
            $table->text('catatan')->nullable();
            $table->timestamp('created_at')->useCurrent(); // append-only: riwayat tidak ditimpa
        });
    }

    public function down(): void
    {
        foreach (['verifikasi_nilai', 'paket_nilai', 'nilai_tp', 'tp', 'periode_nilai'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
