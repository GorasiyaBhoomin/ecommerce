<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_variant_group', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->foreignId('variant_group_id')->constrained('variant_groups')->cascadeOnDelete();

            $table->unique(['category_id', 'variant_group_id'], 'category_variant_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_variant_group');
    }
};
