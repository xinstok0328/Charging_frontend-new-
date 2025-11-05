<?php

use App\Http\Middleware\CustomAuth;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpFoundation\Request as RequestAlias;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'custom.auth' => CustomAuth::class,
        ]);
        $middleware->validateCsrfTokens(
            except: ['*', 'api/*']
        );
        // 信任 ngrok／反向代理，讓 Laravel 正確把當前協議判為 https
        $middleware->trustProxies(
            at: '*',
            headers:
            RequestAlias::HEADER_X_FORWARDED_FOR |
            RequestAlias::HEADER_X_FORWARDED_HOST |
            RequestAlias::HEADER_X_FORWARDED_PORT |
            RequestAlias::HEADER_X_FORWARDED_PROTO
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
