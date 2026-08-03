<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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
        RateLimiter::for('student-login', function (Request $request): array {
            $identifier = strtolower(preg_replace('/\s+/', '', trim((string) $request->input('identifier'))));

            return [
                Limit::perMinute(8)->by($request->ip().'|'.$identifier),
                Limit::perMinute(60)->by($request->ip()),
            ];
        });
    }
}
