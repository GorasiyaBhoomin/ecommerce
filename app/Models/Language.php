<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Throwable;

class Language extends Model
{
    protected $fillable = ['name', 'native_name', 'code', 'is_default', 'is_active'];

    protected $casts = ['is_default' => 'boolean', 'is_active' => 'boolean'];

    /**
     * All active languages (default first).
     */
    public static function active(): Collection
    {
        try {
            return static::where('is_active', true)
                ->orderByDesc('is_default')
                ->orderBy('name')
                ->get();
        } catch (Throwable) {
            return new Collection;
        }
    }

    /**
     * Code of the default language (falls back to "en").
     */
    public static function defaultCode(): string
    {
        try {
            return static::where('is_default', true)->value('code') ?? 'en';
        } catch (Throwable) {
            return 'en';
        }
    }
}
