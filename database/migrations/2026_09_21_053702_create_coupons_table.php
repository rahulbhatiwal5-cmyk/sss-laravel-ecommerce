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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();

            $table->string('code')->unique();

            $table->string('name')->nullable();

            $table->enum('type', [
                'fixed',
                'percentage'
            ]);

            $table->decimal('value', 10, 2);

            $table->decimal('minimum_order_amount', 10, 2)
                ->nullable();

            $table->decimal('maximum_discount_amount', 10, 2)
                ->nullable();

            $table->unsignedInteger('usage_limit')->nullable();

            $table->unsignedInteger('usage_limit_per_user')
                ->default(1);

            $table->timestamp('starts_at')->nullable();

            $table->timestamp('expires_at')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
