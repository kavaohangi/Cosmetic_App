<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('terrain_report_items', function (Blueprint $table) {
            $table->foreignId('client_id')
                ->nullable()
                ->after('terrain_report_id')
                ->constrained('clients')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('terrain_report_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('client_id');
        });
    }
};
