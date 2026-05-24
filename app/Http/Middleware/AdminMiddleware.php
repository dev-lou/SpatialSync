<?php

namespace App\Http\Middleware;

use App\Http\AuthenticatedRequest;
use Closure;
use Symfony\Component\HttpFoundation\Response;


class AdminMiddleware
{
    public function handle(AuthenticatedRequest $request, Closure $next): Response
    {
        if (! $request->auth_user_admin) {
            abort(403, 'Access denied. Admin privileges required.');
        }

        /** @var Response $response */
        $response = $next($request);
        return $response;
    }
}
