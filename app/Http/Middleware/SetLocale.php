<?php

namespace App\Http\Middleware;

use App\Models\Language;
use Closure;
use Illuminate\Http\Request;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $default = Language::defaultCode();
        $codes = Language::active()->pluck('code')->all();

        $locale = session('locale');
        if (! $locale || ! in_array($locale, $codes, true)) {
            $locale = $default;
        }

        app()->setLocale($locale);
        app()->setFallbackLocale($default);

        return $next($request);
    }
}
