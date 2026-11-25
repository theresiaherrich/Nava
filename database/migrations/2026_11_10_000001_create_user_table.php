
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user', function (Blueprint $table) {
            $table->increments('id_user');
            $table->string('username', 50)->unique();
            $table->string('password', 255);
            $table->boolean('must_change_password')->default(true);

            $table->enum('role', [
                'SISWA',
                'GURU',
                'ORANG_TUA',
                'AKADEMIK',
                'KURIKULUM',
                'BUDAYA_POSITIF',
                'KESISWAAN',
                'ADMIN',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user');
    }
};
