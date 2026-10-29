<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'sku',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('position');
    }

    public function specifications(): HasMany
    {
        return $this->hasMany(ProductSpecification::class)->orderBy('position');
    }

    public function parent()
    {
        return $this->belongsTo(Product::class, 'parent_id');
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class, 'product_id');
    }

    protected function priceRange(): Attribute
    {
        return Attribute::get(function (): string {
            $prices = $this->variants
                ->pluck('discounted_price')
                ->map(fn ($price): float => (float) $price);

            if ($prices->isEmpty()) {
                return '—';
            }

            $minimum = $prices->min();
            $maximum = $prices->max();

            if ($minimum === $maximum) {
                return '₹'.number_format($minimum, 2);
            }

            return '₹'.number_format($minimum, 2).' – ₹'.number_format($maximum, 2);
        });
    }

    protected function totalStock(): Attribute
    {
        return Attribute::get(fn (): int => (int) $this->variants->sum('stock_qty'));
    }
}
