<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('songs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artist_id')->constrained()->cascadeOnDelete();
            $table->foreignId('album_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('file_path');
            $table->string('cover_image')->nullable();
            $table->integer('duration')->comment('Duration in seconds');
            $table->unsignedInteger('track_number')->nullable();
            $table->unsignedBigInteger('play_count')->default(0);
            $table->boolean('is_explicit')->default(false);
            $table->boolean('is_available')->default(true);
            $table->timestamps();
            $table->index('artist_id');
            $table->index('album_id');
            $table->index('play_count');
            $table->index('title');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('songs');
    }
};
