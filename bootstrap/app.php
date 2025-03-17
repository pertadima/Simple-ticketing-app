<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Helpers\ApiErrorHelper;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

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
                $apiErrorHelper = new ApiErrorHelper();
                return response()->json($apiErrorHelper->formatError(
                    title: 'Resource Not Found',
                    status: 404,
                    detail: 'The requested resource could not be found. Please check the URL and try again.'
                ), 404);
            }

            throw $e;
        });

        $exceptions->renderable(function (AuthenticationException $e, $request) {
            if ($request->json()) {
                $apiErrorHelper = new ApiErrorHelper();
                return response()->json($apiErrorHelper->formatError(
                    title: 'Unauthenticated',
                    status: 401,
                    detail: 'Authentication credentials are missing or invalid'
                ), 401);
            }

            throw $e;
        });

        $exceptions->renderable(function (MethodNotAllowedHttpException $e, $request) {
            if ($request->json()) {
                $apiErrorHelper = new ApiErrorHelper();
                return response()->json($apiErrorHelper->formatError(
                    title: 'Method not allowed',
                    status: 405,
                    detail: 'The HTTP method used is not allowed for this resource. Please check the allowed methods and try again.',
                    errors: [
                        'requested_method' . ' ' . $request->method(),
                        'allowed_methods' . ' ' . $e->getHeaders()['Allow']
                    ]
                ), 405);
            }

            throw $e;
        });

        $exceptions->renderable(function (AccessDeniedHttpException $e, $request) {
            if ($request->json()) {
                $apiErrorHelper = new ApiErrorHelper();
                return response()->json($apiErrorHelper->formatError(
                    title: 'Access Denied',
                    status: 403,
                    detail: 'You do not have permission to access this resource. Please contact the administrator if you believe this is an error.'
                ), 403);
            }

            throw $e;
        });
    })->create();