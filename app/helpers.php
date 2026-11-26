<?php

use App\Models\Setting;

if (! function_exists('setting')) {
    /**
     * Get or set a system setting.
     */
    function setting(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return Setting::allAsAssoc();
        }

        return Setting::get($key, $default);
    }
}
