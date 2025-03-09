<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\EventsController;
use App\Http\Controllers\API\UsersController;
use App\Http\Controllers\API\AuthController;

Route::prefix('v1')->group(function () {
    Route::apiResource('events', EventsController::class)->only(['index']);
    Route::get('events/{event}', [EventsController::class, 'show']);
    
    Route::get('users/{users}', [UsersController::class, 'show']);
    
    Route::post('auth/login', [AuthController::class, 'login'])->middleware('throttle:3,1');
    Route::post('auth/register', [AuthController::class, 'register'])->middleware('throttle:3,1');
});