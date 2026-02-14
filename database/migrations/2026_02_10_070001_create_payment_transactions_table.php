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
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('payment_intent_id')->constrained()->cascadeOnDelete();
            $table->string('provider_tx_id')->nullable(); // Transaction ID from bank/gateway
            $table->decimal('amount', 10, 2);
            $table->string('payer_name')->nullable();
            $table->string('payer_bank')->nullable();
            $table->json('raw_payload')->nullable(); // Store webhook payload for debugging
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
