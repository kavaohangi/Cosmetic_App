<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_alerts', function (Blueprint $table) {
            $table->text('description')->nullable()->after('quantite_disponible');
            $table->string('source')->default('auto')->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('stock_alerts', function (Blueprint $table) {
            $table->dropColumn(['description', 'source']);
        });
    }
};
