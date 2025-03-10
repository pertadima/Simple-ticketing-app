<?php

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->renderable(function (NotFoundHttpException $e, $request) {
            if ($request->json()) {
                return response()->json(['errors' => [
                    'error_code' => 404,
                    'title' => 'Not Found',
                    'message' => 'Resource not found'
                ]], 404);
            }

            throw $e;
        });

        $exceptions->renderable(function (TooManyRequestsHttpException $e, $request) {
            if ($request->json()) {
                return response()->json(['errors' => [
                    'error_code' => 429,
                    'title' => 'Too many requests',
                    'message' => 'Too many requests, try again later'
                ]], 429);
            }

            throw $e;
        });
    })->create();