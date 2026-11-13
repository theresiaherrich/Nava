<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNavaAuthFieldsToUsersTable extends Migration
{
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique();
            $table->string('email')->nullable()->change();
            $table->string('role')->default('siswa');
            $table->string('sub_role')->nullable();
            $table->boolean('is_active')->default(true);
        });
    }

    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropColumn(['username', 'role', 'sub_role', 'is_active']);
            $table->string('email')->nullable(false)->change();
        });
    }
}