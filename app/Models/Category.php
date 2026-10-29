<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'categories';

    protected $fillable = [
        'name',
        'slug',
        'parent_id',
        'display_order',
        'description',
        'actual_price',
        'discounted_price',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'actual_price' => 'decimal:2',
            'discounted_price' => 'decimal:2',
        ];
    }

    public function variantGroups()
    {
        return $this->belongsToMany(VariantGroup::class, 'category_variant_group');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }
}
