<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('terrain_complaints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->enum('type', ['complaint', 'proposition'])->default('complaint');
            $table->text('description');
            $table->enum('status', ['pending', 'reviewed', 'resolved'])->default('pending');
            $table->text('response')->nullable();
            $table->date('date');
            $table->timestamps();

            $table->index(['user_id', 'date']);
            $table->index(['type', 'status', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('terrain_complaints');
    }
};
