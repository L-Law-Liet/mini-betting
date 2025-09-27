<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            \App\Http\Middleware\HmacSignatureMiddleware::class,
        ]);

        $middleware->alias([
            'auth'           => \Illuminate\Auth\Middleware\Authenticate::class,
            'auth:sanctum'   => \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            'throttle'       => \Illuminate\Routing\Middleware\ThrottleRequests::class,
            'hmac.signature' => \App\Http\Middleware\HmacSignatureMiddleware::class,
            'idempotency'    => \App\Http\Middleware\IdempotencyMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
