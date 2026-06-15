<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_intents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('token_package_id')->constrained('token_packages')->cascadeOnDelete();
            $table->string('gateway');
            $table->string('gateway_transaction_id');
            $table->string('reference');
            $table->unsignedBigInteger('amount_cents');
            $table->string('currency', 3);
            $table->string('status');
            $table->unsignedBigInteger('token_transaction_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['gateway', 'gateway_transaction_id']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_intents');
    }
};
