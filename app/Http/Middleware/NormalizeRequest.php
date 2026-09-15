<?php

namespace App\Http\Middleware;

use App\Http\AuthenticatedRequest;
use Closure;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guarantees the pipeline carries an `App\Http\AuthenticatedRequest`.
 *
 * Every middleware and controller in this app type-hints that class, and
 * `public/index.php` creates the request as that class, so under a normal web
 * request this middleware does nothing at all.
 *
 * Any other entry point — Laravel's own HTTP test client, a queue worker
 * dispatching a synthetic request, a future Octane/Swoole worker — hands the
 * kernel a plain `Illuminate\Http\Request`, and then every guarded route dies
 * with a TypeError and controllers read none of the submitted input. Promoting
 * the request once, before anything else runs, keeps a single request instance:
 * the converted object is rebound in the container and passed down the stack, so
 * middleware that merges auth fields and the controllers that read them are
 * always looking at the same object.
 */
class NormalizeRequest
{
    public function __construct(private readonly Application $app) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($request instanceof AuthenticatedRequest) {
            /** @var Response $response */
            $response = $next($request);

            return $response;
        }

        $converted = AuthenticatedRequest::createFromBase($request);

        // The session is normally attached later by StartSession, but a request
        // that already carries one (a nested/internal dispatch) keeps it.
        if (! $converted->hasSession() && $request->hasSession()) {
            $converted->setLaravelSession($request->session());
        }

        $this->app->instance('request', $converted);

        /** @var Response $response */
        $response = $next($converted);

        return $response;
    }
}
