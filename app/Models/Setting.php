<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'group',
    ];

    /**
     * In-memory cache for fast per-request resolution without hitting cache store repeatedly.
     *
     * @var array<string, string|null>|null
     */
    protected static ?array $runtimeCache = null;

    /**
     * Default settings dictionary.
     *
     * @return array<string, string|null>
     */
    public static function defaults(): array
    {
        return [
            // Store Identity
            'store_name' => 'Shopkart',
            'store_tagline' => 'Everyday Shopping Destination',
            'store_logo' => null,
            'store_favicon' => null,

            // Contact & Localization
            'contact_email' => 'support@shopkart.com',
            'contact_phone' => '+91 98765 43210',
            'contact_address' => '123 Ring Road, Surat, Gujarat, India',
            'currency_symbol' => '₹',
            'currency_code' => 'INR',
            'free_delivery_threshold' => '499',
            'delivery_fee' => '49',

            // Admin Panel Theme Colors
            'admin_primary_color' => '#0F766E',
            'admin_primary_hover' => '#0D655E',
            'admin_sidebar_bg' => '#0F2A27',
            'admin_sidebar_text' => '#CFE3DF',
            'admin_sidebar_active' => '#1D4E49',
            'admin_sidebar_hover' => '#173E3A',
            'admin_accent_color' => '#F59E0B',
            'admin_body_bg' => '#F4F6F5',
            'admin_card_bg' => '#FFFFFF',
        ];
    }

    /**
     * Get all settings as an associative key-value array, falling back to defaults.
     *
     * @return array<string, string|null>
     */
    public static function allAsAssoc(): array
    {
        if (static::$runtimeCache !== null && ! app()->runningUnitTests()) {
            return static::$runtimeCache;
        }

        try {
            if (app()->runningUnitTests()) {
                $dbSettings = static::pluck('value', 'key')->toArray();
            } else {
                $dbSettings = Cache::rememberForever('system_settings', function (): array {
                    return static::pluck('value', 'key')->toArray();
                });
            }
        } catch (\Throwable) {
            $dbSettings = [];
        }

        $merged = array_merge(static::defaults(), $dbSettings);
        static::$runtimeCache = $merged;

        return $merged;
    }

    /**
     * Retrieve a setting value by key.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $all = static::allAsAssoc();

        if (array_key_exists($key, $all) && $all[$key] !== null && $all[$key] !== '') {
            return $all[$key];
        }

        if ($default !== null) {
            return $default;
        }

        return static::defaults()[$key] ?? null;
    }

    /**
     * Store or update a setting.
     */
    public static function set(string $key, mixed $value, string $group = 'general'): self
    {
        $setting = static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group]
        );

        static::forgetCache();

        return $setting;
    }

    /**
     * Clear both runtime and persisted cache.
     */
    public static function forgetCache(): void
    {
        static::$runtimeCache = null;
        try {
            Cache::forget('system_settings');
        } catch (\Throwable) {
            // Ignore in environments where cache is not yet initialized
        }
    }
}
