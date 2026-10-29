<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('variant_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('variant_group_id')->constrained('variant_groups')->cascadeOnDelete();
            $table->string('value');
            $table->string('sku_suffix')->nullable();
            $table->decimal('actual_price', 12, 2)->default(0);
            $table->decimal('discounted_price', 12, 2)->default(0);
            $table->unsignedInteger('stock_qty')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('variant_values');
    }
};
