<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use App\Helpers\ApiErrorHelper;

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
        //
        $this->configureRateLimiting();
    }

    protected function configureRateLimiting()
    {
        // Login rate limiter: 5 attempts per minute
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)
                ->by($request->ip() . '|' . $request->email)
                ->response(function (Request $request, array $headers) {
                    $apiErrorHelper = new ApiErrorHelper();
                    return response()->json($apiErrorHelper->formatError(
                        title: 'Too many requests',
                        status: 429,
                        detail: 'Too many requests, try again later after ' . $headers['Retry-After'] . ' seconds',
                    ), 429, $headers);
                });
        });

        // Registration rate limiter: 3 attempts per hour
        RateLimiter::for('register', function (Request $request) {
            return Limit::perHour(20)
                ->by($request->ip())
                ->response(function (Request $request, array $headers) {
                    $apiErrorHelper = new ApiErrorHelper();
                    return response()->json($apiErrorHelper->formatError(
                        title: 'Too many requests',
                        status: 429,
                        detail: 'Too many requests, try again later after ' . $headers['Retry-After'] . ' seconds',
                    ), 429, $headers);
                });
        });
}
}
