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

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('customer_name');
            $table->string('phone', 20);
            $table->text('address');
            $table->string('city');
            $table->string('state');
            $table->string('postal_code', 10);
            $table->string('landmark')->nullable();

            $table->decimal('subtotal', 12, 2);
            $table->decimal('delivery_fee', 12, 2)->default(49);
            $table->decimal('total_amount', 12, 2);

            $table->string('payment_method')->default('cod');
            $table->string('payment_status')->default('COD');
            $table->string('order_status')->default('pending');

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
