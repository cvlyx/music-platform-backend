<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('tx_ref')->unique();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 10)->default('MWK');
            $table->string('plan', 20)->default('premium');
            $table->string('status', 20)->default('pending');
            $table->string('method', 30)->nullable();
            $table->json('paychangu_data')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
