<?php

use App\Http\Middleware\FakultasScopeMiddleware;
use App\Http\Middleware\RequestLoggingMiddleware;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\SecurityHeadersMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Global middleware - applies to all requests
        $middleware->append(SecurityHeadersMiddleware::class);

        // Middleware aliases
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'log.requests' => RequestLoggingMiddleware::class,
            'fakultas.scope' => FakultasScopeMiddleware::class,
        ]);

        // Webhook Midtrans dipanggil oleh server Midtrans, bukan browser,
        // sehingga harus dikecualikan dari CSRF verification.
        $middleware->validateCsrfTokens(except: [
            'midtrans/notification',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
