<?php

namespace App\Providers;

use App\Models\Orders;
use App\Policies\OrdersPolicy;
use Illuminate\Support\ServiceProvider;

class AuthServicesProvider extends ServiceProvider
{
    protected $policies = [
        Orders::class => OrdersPolicy::class,
    ];
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
