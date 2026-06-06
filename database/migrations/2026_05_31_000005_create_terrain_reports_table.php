<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('terrain_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('supervisor_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->date('date');
            $table->unsignedInteger('nb_ventes')->default(0);
            $table->text('plaintes_clients')->nullable();
            $table->text('propositions_clients')->nullable();
            $table->boolean('rupture_stock')->default(false);
            $table->json('produits_rupture')->nullable();
            $table->string('photo_url')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'date']);
            $table->index(['supervisor_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('terrain_reports');
    }
};
