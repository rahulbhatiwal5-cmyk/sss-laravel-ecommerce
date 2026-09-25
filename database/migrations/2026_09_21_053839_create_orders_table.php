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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->string('order_number')->unique();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('coupon_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            /*
    |--------------------------------------------------------------------------
    | Customer snapshot
    |--------------------------------------------------------------------------
    */

            $table->string('customer_name');

            $table->string('customer_email')->nullable();

            $table->string('customer_phone', 20);

            /*
    |--------------------------------------------------------------------------
    | Shipping address snapshot
    |--------------------------------------------------------------------------
    */

            $table->string('shipping_address');

            $table->string('shipping_address_2')->nullable();

            $table->string('shipping_city');

            $table->string('shipping_state');

            $table->string('shipping_postal_code');

            $table->string('shipping_country')
                ->default('India');

            /*
    |--------------------------------------------------------------------------
    | Amounts
    |--------------------------------------------------------------------------
    */

            $table->decimal('subtotal', 10, 2);

            $table->decimal('discount_amount', 10, 2)
                ->default(0);

            $table->decimal('shipping_amount', 10, 2)
                ->default(0);

            $table->decimal('tax_amount', 10, 2)
                ->default(0);

            $table->decimal('total_amount', 10, 2);

            /*
    |--------------------------------------------------------------------------
    | Order
    |--------------------------------------------------------------------------
    */

            $table->enum('payment_method', [
                'razorpay',
                'cod'
            ]);

            $table->enum('payment_status', [
                'pending',
                'paid',
                'failed',
                'refunded',
                'partially_refunded'
            ])->default('pending');

            $table->enum('order_status', [
                'pending',
                'confirmed',
                'processing',
                'shipped',
                'out_for_delivery',
                'delivered',
                'cancelled',
                'returned'
            ])->default('pending');

            $table->text('customer_note')->nullable();

            $table->text('admin_note')->nullable();

            $table->timestamp('confirmed_at')->nullable();

            $table->timestamp('shipped_at')->nullable();

            $table->timestamp('delivered_at')->nullable();

            $table->timestamp('cancelled_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
