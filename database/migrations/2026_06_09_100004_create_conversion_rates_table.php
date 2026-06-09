<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversion_rates', function (Blueprint $table) {
            $table->id();
            $table->decimal('taux_fc', 12, 4);
            $table->foreignId('defined_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('actif')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversion_rates');
    }
};
