<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\BotSeoMiddleware;
use App\Http\Middleware\CheckBuildPermission;
use App\Http\Middleware\RedirectIfSupabaseAuthenticated;
use App\Http\Middleware\SupabaseAuthenticate;
use App\Providers\ViewServiceProvider;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Session\Middleware\StartSession;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withProviders([
        ViewServiceProvider::class,
    ])
    ->withMiddleware(function (Middleware $middleware) {
        // Intercept social media bots FIRST — before sessions, CSRF, or auth
        $middleware->prepend(BotSeoMiddleware::class);

        $middleware->trustProxies(at: '*');

        $middleware->api(prepend: [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            VerifyCsrfToken::class,
        ]);

        // Register middleware aliases - override Laravel defaults for Supabase auth
        $middleware->alias([
            'auth' => SupabaseAuthenticate::class,
            'guest' => RedirectIfSupabaseAuthenticated::class,
            'admin' => AdminMiddleware::class,
            'build.permission' => CheckBuildPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
