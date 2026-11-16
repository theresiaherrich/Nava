<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // Tabel akun bersama A1-A4 (nama tabel mengikuti siakad_sma1_revisi.sql).
        Schema::create('user', function (Blueprint $table) {
            $table->increments('id_user');
            $table->string('username', 50)->unique();
            $table->string('password', 255);
            $table->string('nama', 120);
            $table->string('email', 100)->nullable()->unique();
            $table->enum('status_akun', ['pending', 'aktif', 'nonaktif'])->default('pending');
            $table->boolean('must_change_password')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        // Peran dibuat terpisah agar satu akun bisa punya banyak peran (sesuai desain & PRD).
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

        // Token reset kata sandi disimpan dalam bentuk hash SHA-256.
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('id_user');
            $table->string('token_hash', 64)->unique();
            $table->timestamp('expires_at');
            $table->timestamp('created_at')->nullable();
            $table->foreign('id_user')->references('id_user')->on('user')->cascadeOnDelete();
        });

        Schema::create('log_aktivitas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('id_user');
            $table->string('aksi', 50);
            $table->string('nama_entitas', 50);
            $table->unsignedBigInteger('entitas_id');
            $table->text('alasan')->nullable();
            $table->json('data_sebelum')->nullable();
            $table->json('data_sesudah')->nullable();
            $table->timestamps();
            $table->foreign('id_user')->references('id_user')->on('user')->restrictOnDelete();
            $table->index(['nama_entitas', 'entitas_id'], 'idx_log_entitas');
            $table->index(['id_user', 'created_at'], 'idx_log_user_created');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_aktivitas');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('user');
        Schema::dropIfExists('sessions');
    }
};
