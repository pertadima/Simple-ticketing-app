<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\EventsController;
use App\Http\Controllers\API\UsersController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\OrdersController;

Route::prefix('v1')->group(function () {
    Route::apiResource('events', EventsController::class)->only(['index']);
    Route::get('events/{event}', [EventsController::class, 'show']);
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('users/{users}', [UsersController::class, 'show']);
        Route::get('users/{user}/orders', [UsersController::class, 'orders']);
        Route::post('users/{user}/logout', [AuthController::class, 'logout']);

        Route::post('orders/create', [OrdersController::class, 'store']);
        Route::patch('orders/{order}/pay', [OrdersController::class, 'markAsPaid'])->name('orders.pay');
    });
    
    Route::post('auth/login', [AuthController::class, 'login'])->middleware('throttle:login');
    Route::post('auth/register', [AuthController::class, 'register'])->middleware('throttle:register');
});