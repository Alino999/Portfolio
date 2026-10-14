<?php

namespace App\Providers;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

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
        Schema::defaultStringLength(191);

        View::composer(['layouts.admin', 'layouts.guest', 'layouts.navigation'], function ($view): void {
            $settings = Schema::hasTable('site_settings')
                ? SiteSetting::query()->pluck('value', 'key')
                : collect();

            $view->with('brandSettings', $settings);
        });

        if (app()->environment('local', 'testing')) {
            URL::forceScheme('http');
        } elseif (str_starts_with((string) config('app.url', 'http://localhost'), 'https://')) {
            URL::forceScheme('https');
        }
    }
}
