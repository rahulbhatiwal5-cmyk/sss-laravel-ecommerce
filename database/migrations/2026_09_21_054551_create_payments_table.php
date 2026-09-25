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

            $table->foreignId('order_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('provider')
                ->default('razorpay');

            /*
    |--------------------------------------------------------------------------
    | Razorpay IDs
    |--------------------------------------------------------------------------
    */

            $table->string('razorpay_order_id')
                ->nullable()
                ->unique();

            $table->string('razorpay_payment_id')
                ->nullable()
                ->unique();

            $table->string('razorpay_signature')
                ->nullable();

            /*
    |--------------------------------------------------------------------------
    | Payment
    |--------------------------------------------------------------------------
    */

            $table->decimal('amount', 10, 2);

            $table->string('currency', 3)
                ->default('INR');

            $table->string('method')->nullable();

            $table->enum('status', [
                'created',
                'authorized',
                'captured',
                'failed',
                'refunded',
                'partially_refunded'
            ])->default('created');

            $table->string('bank')->nullable();

            $table->string('wallet')->nullable();

            $table->string('vpa')->nullable();

            $table->string('card_last4', 4)
                ->nullable();

            $table->text('failure_reason')->nullable();

            $table->json('gateway_response')
                ->nullable();

            $table->timestamp('paid_at')
                ->nullable();

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
