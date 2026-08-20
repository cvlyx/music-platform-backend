<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('stage_name');
            $table->text('bio')->nullable();
            $table->string('image')->nullable();
            $table->string('banner_image')->nullable();
            $table->boolean('verified')->default(false);
            $table->unsignedBigInteger('monthly_listeners')->default(0);
            $table->timestamps();
            $table->index('verified');
            $table->index('monthly_listeners');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artists');
    }
};
