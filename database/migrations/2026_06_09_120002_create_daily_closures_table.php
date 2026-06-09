<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_closures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('day_id')->constrained('day_entities')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role');
            $table->decimal('ventes_credit', 12, 2)->default(0);
            $table->decimal('ventes_comptant', 12, 2)->default(0);
            $table->json('payload')->nullable();
            $table->timestamp('closed_at');
            $table->timestamps();

            $table->unique(['day_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_closures');
    }
};
