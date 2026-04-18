<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\RedirectIfSupabaseAuthenticated;
use App\Http\Middleware\SupabaseAuthenticate;
use App\Providers\ViewServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

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
        $middleware->api(prepend: [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
        ]);

        // Register middleware aliases - override Laravel defaults for Supabase auth
        $middleware->alias([
            'auth' => SupabaseAuthenticate::class,
            'guest' => RedirectIfSupabaseAuthenticated::class,
            'admin' => AdminMiddleware::class,
            'build.permission' => \App\Http\Middleware\CheckBuildPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
