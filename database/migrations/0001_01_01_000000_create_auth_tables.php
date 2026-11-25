<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Akun, peran, sesi, audit, dan notifikasi.
 * Tabel akun bernama `user` (PK id_user) karena dipakai bersama modul A1–A4.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user', function (Blueprint $table) {
            $table->increments('id_user');
            $table->string('username', 50)->unique();
            $table->string('password');
            $table->string('nama', 120);
            $table->string('email', 100)->nullable()->unique();
            $table->enum('status_akun', ['belum_aktif', 'aktif', 'nonaktif'])->default('belum_aktif');
            $table->boolean('must_change_password')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->increments('id_role');
            $table->string('kode', 30)->unique();
            $table->string('nama', 60);
        });

        Schema::create('role_user', function (Blueprint $table) {
            $table->unsignedInteger('id_user');
            $table->unsignedInteger('id_role');
            $table->timestamp('created_at')->nullable();
            $table->primary(['id_user', 'id_role']);
            $table->foreign('id_user')->references('id_user')->on('user')->cascadeOnDelete();
            $table->foreign('id_role')->references('id_role')->on('roles')->cascadeOnDelete();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->unsignedInteger('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // id_user null = aksi sistem/CLI (mis. seeder).
        Schema::create('log_aktivitas', function (Blueprint $table) {
            $table->bigIncrements('id_log');
            $table->unsignedInteger('id_user')->nullable();
            $table->string('aksi', 50);
            $table->string('nama_entitas', 50);
            $table->unsignedBigInteger('entitas_id')->nullable();
            $table->text('alasan')->nullable();
            $table->json('data_sebelum')->nullable();
            $table->json('data_sesudah')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->foreign('id_user')->references('id_user')->on('user')->nullOnDelete();
            $table->index(['nama_entitas', 'entitas_id'], 'idx_log_entitas');
            $table->index(['id_user', 'created_at'], 'idx_log_user_created');
        });

        Schema::create('notifikasi', function (Blueprint $table) {
            $table->bigIncrements('id_notifikasi');
            $table->unsignedInteger('id_user');
            $table->string('jenis', 50);
            $table->string('judul', 150);
            $table->text('pesan');
            $table->json('data')->nullable();
            $table->timestamp('dibaca_at')->nullable();
            $table->timestamps();
            $table->foreign('id_user')->references('id_user')->on('user')->cascadeOnDelete();
            $table->index(['id_user', 'dibaca_at'], 'idx_notif_user_baca');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifikasi');
        Schema::dropIfExists('log_aktivitas');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('user');
    }
};
