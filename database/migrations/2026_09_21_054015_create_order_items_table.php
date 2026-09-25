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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('product_variant_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            /*
    |--------------------------------------------------------------------------
    | Product snapshot
    |--------------------------------------------------------------------------
    */

            $table->string('product_name');

            $table->string('sku')->nullable();

            $table->string('size')->nullable();

            $table->string('color')->nullable();

            $table->string('image')->nullable();

            /*
    |--------------------------------------------------------------------------
    | Price
    |--------------------------------------------------------------------------
    */

            $table->decimal('price', 10, 2);

            $table->unsignedInteger('quantity');

            $table->decimal('total', 10, 2);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
