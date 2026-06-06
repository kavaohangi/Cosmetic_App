<?php

use App\Enums\OrderStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('statut')->default(OrderStatus::EnAttente->value)->index();
            $table->decimal('total', 12, 2)->default(0);
            $table->date('date_commande');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['statut', 'date_commande']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
