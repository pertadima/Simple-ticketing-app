<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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

        $exceptions->renderable(function (AuthenticationException $e, $request) {
            if ($request->json()) {
                return response()->json(['errors' => [
                    'error_code' => 401,
                    'title' => 'Unauthorized',
                    'message' => 'Token invalid'
                ]], 401);
            }

            throw $e;
        });
    })->create();