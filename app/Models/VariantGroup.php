<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VariantGroup extends Model
{
    protected $fillable = ['name', 'label', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function values(): HasMany
    {
        return $this->hasMany(VariantValue::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_variant_group');
    }
}
