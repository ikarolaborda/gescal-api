<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function () {
            // API V2 routes
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api_v2.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Register middleware aliases
        $middleware->alias([
            'jwt.auth' => \App\Http\Middleware\JwtAuthentication::class,
            'role.check' => \App\Http\Middleware\RoleAuthorization::class,
            'jsonapi.headers' => \App\Http\Middleware\JsonApiHeaders::class,
            'api.deprecation' => \App\Http\Middleware\ApiDeprecationMiddleware::class,
        ]);

        // Apply deprecation middleware to all API routes
        $middleware->api(append: [
            \App\Http\Middleware\ApiDeprecationMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
