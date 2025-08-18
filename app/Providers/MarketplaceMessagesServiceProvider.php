<?php

namespace App\Providers;

use App\Services\MarketplaceMessagesService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class MarketplaceMessagesServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(MarketplaceMessagesService::class, function (Application $app) {
            return new MarketplaceMessagesService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
