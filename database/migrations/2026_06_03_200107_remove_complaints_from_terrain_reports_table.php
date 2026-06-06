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
        Schema::table('terrain_reports', function (Blueprint $table) {
            $table->dropColumn([
                'plaintes_clients',
                'propositions_clients',
                'rupture_stock',
                'produits_rupture',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('terrain_reports', function (Blueprint $table) {
            $table->text('plaintes_clients')->nullable();
            $table->text('propositions_clients')->nullable();
            $table->boolean('rupture_stock')->default(false);
            $table->json('produits_rupture')->nullable();
        });
    }
};
