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
        Schema::create('returns', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('order_item_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unsignedInteger('quantity')
                ->default(1);

            $table->string('reason');

            $table->text('description')
                ->nullable();

            $table->enum('status', [
                'requested',
                'approved',
                'rejected',
                'picked_up',
                'received',
                'refunded'
            ])->default('requested');

            $table->decimal('refund_amount', 10, 2)
                ->nullable();

            $table->text('admin_note')
                ->nullable();

            $table->timestamp('approved_at')
                ->nullable();

            $table->timestamp('received_at')
                ->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('returns');
    }
};
