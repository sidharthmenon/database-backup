<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Auth\Access\Gate as GateConcrete;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->singleton(
            \Illuminate\Contracts\Log\ContextLogProcessor::class,
            fn () => new \Illuminate\Log\Context\ContextLogProcessor()
        );
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Provide a Gate that works in CLI (no authenticated user).
        $this->app->singleton(GateContract::class, function ($app) {
            $gate = new GateConcrete($app, function () {
                // No authenticated user in console; return null or a "system" user if you have one.
                return null;
            });

            // If you want to allow everything in console:
            // $gate->before(fn($user, $ability) => true);

            return $gate;
        });
    }
}
