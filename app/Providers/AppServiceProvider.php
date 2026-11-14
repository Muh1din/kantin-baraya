<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Order;
use App\Observers\OrderObserver;
use App\Models\OrderItem;
use App\Observers\OrderItemObserver;
use App\Models\PaymentSetting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

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
        Order::observe(OrderObserver::class);
        OrderItem::observe(OrderItemObserver::class);

        View::composer('components.customer.card-drawer', function ($view): void {
            $path = PaymentSetting::query()->value('qris_image_path');
            $view->with('qrisImageUrl', $path ? Storage::disk('public')->url($path) : null);
        });
    }
}
