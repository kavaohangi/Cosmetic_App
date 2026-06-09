<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('goods_issue_notes', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('delivery_id')->nullable()->constrained('deliveries')->nullOnDelete();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('date');
            $table->string('motif')->nullable();
            $table->timestamps();
        });

        Schema::create('goods_issue_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goods_issue_note_id')->constrained('goods_issue_notes')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->integer('quantite');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goods_issue_lines');
        Schema::dropIfExists('goods_issue_notes');
    }
};
