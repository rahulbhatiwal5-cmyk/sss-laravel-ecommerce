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
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('color_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('size_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('sku')->unique();

            $table->decimal('price', 10, 2)->nullable();

            $table->decimal('sale_price', 10, 2)->nullable();

            $table->unsignedInteger('stock')->default(0);

            $table->unsignedInteger('low_stock_limit')->default(5);

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique(
                ['product_id', 'color_id', 'size_id'],
                'unique_product_variant'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
