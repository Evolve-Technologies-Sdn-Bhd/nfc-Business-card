<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Add Activity Logging middleware to API routes
        $middleware->api(append: [
            \App\Http\Middleware\LogActivity::class,
            \App\Http\Middleware\HandleCors::class,
        ]);

        // Add CORS middleware to web routes as well for better compatibility
        $middleware->web(append: [
            \App\Http\Middleware\HandleCors::class,
        ]);

        // Register admin middleware alias
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
        ]);

        // Handle unauthenticated API requests
        $middleware->redirectGuestsTo(function () {
            if (request()->expectsJson() || request()->is('api/*')) {
                abort(401, 'Unauthenticated');
            }
            return route('login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
