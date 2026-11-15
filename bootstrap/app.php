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
    ->withMiddleware(function (Middleware $middleware) {
        // Di belakang reverse proxy (nginx/Cloudflare), header X-Forwarded-* diperlukan
        // agar isSecure(), asset() dan route() tetap HTTPS. Persempit '*' ke IP proxy bila perlu.
        $middleware->trustProxies(at: '*');

        // Mendaftarkan alias middleware role
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);

        // Header keamanan global (nosniff, anti-clickjacking, referrer policy, dll)
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
