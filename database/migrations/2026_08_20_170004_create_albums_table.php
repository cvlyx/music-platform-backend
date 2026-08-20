<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('albums', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artist_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('cover_image')->nullable();
            $table->date('release_date')->nullable();
            $table->enum('type', ['album', 'single', 'ep', 'compilation'])->default('album');
            $table->string('label')->nullable();
            $table->timestamps();
            $table->index('release_date');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('albums');
    }
};
