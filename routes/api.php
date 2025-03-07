<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\EventsController;
use App\Http\Controllers\API\TicketsController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('v1/events', EventsController::class)->only(['index', 'show']);
Route::get('v1/events/{event}/tickets', [TicketsController::class, 'index']);