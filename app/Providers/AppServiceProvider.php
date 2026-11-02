<?php

namespace App\Providers;

use App\Models\Language;
use App\Models\Order;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->ensureApplicationEncryptionKey();
    }

    /**
     * Hosts that import .env.example often ship an empty APP_KEY, which makes every request 500.
     */
    private function ensureApplicationEncryptionKey(): void
    {
        $config = $this->app['config'];
        $cipher = (string) $config->get('app.cipher');
        $key = (string) $config->get('app.key');

        if ($this->applicationEncryptionKeyIsValid($key, $cipher)) {
            return;
        }

        $path = storage_path('app/app.key');
        $stored = is_readable($path) ? trim((string) file_get_contents($path)) : '';

        if (! $this->applicationEncryptionKeyIsValid($stored, $cipher)) {
            $stored = 'base64:'.base64_encode(random_bytes(32));
            $directory = dirname($path);

            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            file_put_contents($path, $stored, LOCK_EX);
        }

        $config->set('app.key', $stored);
    }

    private function applicationEncryptionKeyIsValid(string $key, string $cipher): bool
    {
        if (str_starts_with($key, 'base64:')) {
            $decoded = base64_decode(substr($key, 7), true);

            if ($decoded === false) {
                return false;
            }

            $key = $decoded;
        }

        $length = strlen($key);

        return match (strtolower($cipher)) {
            'aes-128-cbc', 'aes-128-gcm' => $length === 16,
            'aes-256-cbc', 'aes-256-gcm' => $length === 32,
            default => false,
        };
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('admin.partials.notification-bar', function ($view) {
            $newOrders = Order::withCount('items')
                ->where('order_status', 'pending')
                ->latest()
                ->take(5)
                ->get();

            $activeLanguages = Language::active();
            $currentLocale = app()->getLocale();

            $view->with([
                'newOrders' => $newOrders,
                'activeLanguages' => $activeLanguages,
                'currentLocale' => $currentLocale,
            ]);
        });
    }
}
