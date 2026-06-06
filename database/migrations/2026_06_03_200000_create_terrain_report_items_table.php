<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('terrain_report_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('terrain_report_id')->constrained('terrain_reports')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->unsignedInteger('quantite');
            $table->decimal('prix_unitaire', 12, 2)->default(0);
            $table->decimal('sous_total', 12, 2)->default(0);
            $table->timestamps();

            $table->index(['terrain_report_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('terrain_report_items');
    }
};
