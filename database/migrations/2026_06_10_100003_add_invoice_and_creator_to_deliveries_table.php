<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            $table->foreignId('created_by')
                ->nullable()
                ->after('agent_id')
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('invoice_id')
                ->nullable()
                ->after('created_by')
                ->constrained('invoices')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('invoice_id');
            $table->dropConstrainedForeignId('created_by');
        });
    }
};
