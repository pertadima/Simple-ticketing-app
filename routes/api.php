<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\EventsController;
use App\Http\Controllers\API\TicketsController;
use App\Http\Controllers\API\UsersController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('v1/events', EventsController::class)->only(['index']);
Route::get('v1/events/{event}', [EventsController::class, 'show']);

Route::get('v1/users/{users}', [UsersController::class, 'show']);