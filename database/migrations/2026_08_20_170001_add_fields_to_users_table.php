<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->unique()->after('name');
            $table->string('avatar')->nullable()->after('username');
            $table->enum('role', ['user', 'artist', 'admin'])->default('user')->after('avatar');
            $table->string('country')->nullable()->after('role');
            $table->timestamp('last_login_at')->nullable()->after('country');
            $table->boolean('is_premium')->default(false)->after('last_login_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['username', 'avatar', 'role', 'country', 'last_login_at', 'is_premium']);
        });
    }
};
