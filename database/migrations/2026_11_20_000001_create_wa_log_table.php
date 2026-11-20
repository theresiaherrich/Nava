<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wa_log', function (Blueprint $t) {
            $t->id();
            $t->unsignedInteger('id_siswa');
            $t->string('hubungan', 10)->nullable();
            $t->string('nomor_samar', 20)->nullable();
            $t->string('template', 60);
            $t->string('kunci', 100)->unique();   // idempotency key
            $t->string('status', 20);             // diproses | diterima | gagal
            $t->string('error', 120)->nullable();
            $t->timestamps();
            $t->index(['id_siswa', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wa_log');
    }
};