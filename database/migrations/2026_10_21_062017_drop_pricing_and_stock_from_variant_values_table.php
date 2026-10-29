<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('variant_values', function (Blueprint $table) {
            $table->dropColumn(['actual_price', 'discounted_price', 'stock_qty']);
        });
    }

    public function down(): void
    {
        Schema::table('variant_values', function (Blueprint $table) {
            $table->decimal('actual_price', 12, 2)->default(0);
            $table->decimal('discounted_price', 12, 2)->default(0);
            $table->unsignedInteger('stock_qty')->default(0);
        });
    }
};
