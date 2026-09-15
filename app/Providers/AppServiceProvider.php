<?php

namespace App\Providers;

use App\Http\AuthenticatedRequest;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

/**
 * Makes the live HTTP request the one the container hands out.
 *
 * Middleware and controllers type-hint `App\Http\AuthenticatedRequest`. Laravel
 * resolves unknown classes by building a fresh instance, so without this binding
 * they would receive a *blank* request — no submitted input, no session, no
 * merged auth fields. `public/index.php` creates the request as
 * `AuthenticatedRequest` and the HTTP kernel registers it under the `request`
 * key, so resolving through that key returns the real instance for every entry
 * point.
 *
 * Entry points that do not build an `AuthenticatedRequest` themselves are
 * handled by `App\Http\Middleware\NormalizeRequest`, which promotes the request
 * before the rest of the pipeline runs.
 */
class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AuthenticatedRequest::class, function (Application $app): mixed {
            return $app->make('request');
        });
    }

    public function boot(): void
    {
        //
    }
}
