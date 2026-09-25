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
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->foreignId('category_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('brand_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('name');

            $table->string('slug')->unique();

            $table->string('sku')->nullable()->unique();

            $table->text('short_description')->nullable();

            $table->longText('description')->nullable();

            $table->text('material')->nullable();

            $table->text('care_instructions')->nullable();

            $table->string('gender')->nullable();

            $table->decimal('price', 10, 2);

            $table->decimal('sale_price', 10, 2)->nullable();

            $table->boolean('is_featured')->default(false);

            $table->boolean('is_active')->default(true);

            $table->timestamp('published_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
