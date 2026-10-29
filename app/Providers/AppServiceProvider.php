<?php

namespace App\Providers;

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
        //
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

            $view->with('newOrders', $newOrders);
        });
    }
}
