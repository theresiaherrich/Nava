<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reset_password_tokens', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('id_user')->unique();
            $table->string('token', 64)->unique();
            $table->timestamp('expires_at');

            $table->timestamps();

            $table->foreign('id_user')
                ->references('id_user')
                ->on('user')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reset_password_tokens');
    }
};