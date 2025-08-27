<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        config(['logging.channels.single.path' => \Phar::running()
            ? dirname(\Phar::running(false)) . '/logs/laravel.log'
            : storage_path('logs/laravel.log')
        ]);

        config(['logging.channels.daily.path' => \Phar::running()
            ? dirname(\Phar::running(false)) . '/logs/laravel.log'
            : storage_path('logs/laravel.log')
        ]);
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }
}
