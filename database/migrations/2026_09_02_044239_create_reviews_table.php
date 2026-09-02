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
    Schema::create('reviews', function (Blueprint $table) {
        $table->id();

        $table->foreignId('customer_id')
            ->constrained('customers')
            ->cascadeOnDelete();

        $table->foreignId('order_id')
            ->constrained('orders')
            ->cascadeOnDelete();

        $table->unsignedTinyInteger('rating');

        $table->text('comment')->nullable();

        $table->boolean('is_published')->default(false);

        $table->timestamps();

        $table->unique(['customer_id', 'order_id']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
