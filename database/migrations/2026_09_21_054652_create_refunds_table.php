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
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('payment_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('razorpay_refund_id')
                ->nullable()
                ->unique();

            $table->decimal('amount', 10, 2);

            $table->enum('status', [
                'pending',
                'processed',
                'failed'
            ])->default('pending');

            $table->text('reason')->nullable();

            $table->json('gateway_response')
                ->nullable();

            $table->timestamp('refunded_at')
                ->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
