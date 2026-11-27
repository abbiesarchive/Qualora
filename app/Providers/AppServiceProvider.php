<?php

namespace App\Providers;

use App\Services\RealtimeBroadcaster;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // One instance per request so realtime hints are batched into a single call.
        $this->app->singleton(RealtimeBroadcaster::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
