<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class cart_items extends Model
{
    protected $fillable = [
        'user_id',
        'product_id',
        'variant_id',
        'quantity',
    ];

    // Cart item belongs to a cart

    // Cart item belongs to a product
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    // Cart item belongs to a product variant
    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
