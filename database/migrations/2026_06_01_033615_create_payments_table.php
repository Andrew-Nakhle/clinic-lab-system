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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('appointment_id')->constrained()->onDelete('cascade');
            $table->string('stripe_payment_intent_id')->nullable()->unique();
            $table->string('payment_method');
            $table->string('provider');
            $table->string('status');//pending,paid,failed,refunded
            $table->decimal('amount', 10, 2);
            $table->decimal('refunded_amount', 10, 2)->default(0);
            $table->decimal('retained_amount', 10, 2)->default(0);
            $table->string('currency');
            $table->json('metadata')->nullable();

            $table->timestamp('completed_at')->nullable();
            $table->timestamp('refunded_at')->nullable();

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
