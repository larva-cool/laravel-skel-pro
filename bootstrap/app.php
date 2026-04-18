<?php
/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Psr\Log\LogLevel;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: [
            __DIR__.'/../routes/api_v1.php',
            __DIR__.'/../routes/api_v2.php'
        ],
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();
        // $middleware->authenticateSessions();
        // $middleware->throttleWithRedis();
        $middleware->alias([
            'abilities' => Laravel\Sanctum\Http\Middleware\CheckAbilities::class,
            'ability' => Laravel\Sanctum\Http\Middleware\CheckForAnyAbility::class,
        ]);
        $middleware->web(append: [
            \App\Http\Middleware\RefreshUserActiveAt::class,
        ]);
        $middleware->api(prepend: [
            \App\Http\Middleware\RefreshUserActiveAt::class,
        ]);
        // Configure the CSRF token validation middleware.
        $middleware->validateCsrfTokens([
            '/api/*',
        ]);
        // Configure the cookie encryption middleware.
        // $middleware->encryptCookies([
        //     //
        // ]);
        // 配置信任的代理 IP
        $middleware->trustProxies(at: [
            '127.0.0.1',
            '10.0.0.0/8',
            '100.64.0.0/10',
            '172.16.0.0/16',
            '192.168.0.0/16',
        ]);
        // 配置信任的代理头
        $middleware->trustProxies(headers: \Illuminate\Http\Request::HEADER_X_FORWARDED_FOR |
            \Illuminate\Http\Request::HEADER_X_FORWARDED_HOST |
            \Illuminate\Http\Request::HEADER_X_FORWARDED_PORT |
            \Illuminate\Http\Request::HEADER_X_FORWARDED_PROTO |
            \Illuminate\Http\Request::HEADER_X_FORWARDED_PREFIX |
            \Illuminate\Http\Request::HEADER_X_FORWARDED_AWS_ELB
        );
        // Configure the URL signature validation middleware.
        // $middleware->validateSignatures([
        //     'fbclid',
        //     'utm_campaign',
        //     'utm_content',
        //     'utm_medium',
        //     'utm_source',
        //     'utm_term',
        // ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->level(\PDOException::class, LogLevel::CRITICAL);
        $exceptions->dontReportDuplicates();
    })->create();
