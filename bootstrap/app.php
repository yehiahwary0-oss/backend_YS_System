<?php

use App\Http\Middleware\CookieToBearer;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\ForceJsonResponse;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__ . '/../routes/api.php',
        apiPrefix: 'api/v1',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Applied to every request
        $middleware->append(SecurityHeaders::class);
        $middleware->append(ForceJsonResponse::class);
        $middleware->append(CookieToBearer::class);

        // Named middleware aliases
        $middleware->alias([
            'active' => EnsureUserIsActive::class,
        ]);

        // Sanctum stateful domains (cookie-based auth for SPA)
        $middleware->statefulApi();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Laravel 12 replaces the App\Exceptions\Handler class-based approach
        // with closures registered here. This renders() call provides the
        // consistent { success, message, code, errors } JSON shape used
        // across the entire API for every exception type.
        $exceptions->render(function (ValidationException $e, $request) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'code'    => 'VALIDATION_ERROR',
                'errors'  => $e->errors(),
            ], 422);
        });

        $exceptions->render(function (AuthenticationException $e, $request) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
                'code'    => 'UNAUTHENTICATED',
            ], 401);
        });

        $exceptions->render(function (HttpException $e, $request) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'An error occurred.',
                'code'    => 'HTTP_ERROR',
            ], $e->getStatusCode());
        });

        $exceptions->render(function (Throwable $e, $request) {
            $status  = 500;
            $message = app()->environment('production')
                ? 'An unexpected error occurred.'
                : $e->getMessage();

            return response()->json([
                'success' => false,
                'message' => $message,
                'code'    => 'SERVER_ERROR',
            ], $status);
        });
    })
    ->withProviders([
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
    ])
    ->create();
