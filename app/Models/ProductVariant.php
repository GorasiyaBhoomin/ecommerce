<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'sku',
        'actual_price',
        'discounted_price',
        'stock_qty',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'actual_price' => 'decimal:2',
            'discounted_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function values()
    {
        return $this->belongsToMany(
            VariantValue::class,
            'product_variant_values',
            'product_variant_id',
            'variant_value_id'
        );
    }
}
