<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user', function (Blueprint $t) {
            $t->increments('id_user');
            $t->string('username', 50)->unique();
            $t->string('password', 255);
            $t->boolean('must_change_password')->default(true);
            $t->enum('role', ['SISWA', 'GURU', 'ORANG_TUA', 'AKADEMIK', 'KURIKULUM', 'BUDAYA_POSITIF', 'KESISWAAN', 'ADMIN']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user');
    }
};
